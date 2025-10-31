/**
 * =========================================
 * Level 5: K6 Load Test (Production-Grade)
 * Advanced load testing with scenarios
 * =========================================
 * 
 * Install k6:
 *   macOS:   brew install k6
 *   Linux:   https://k6.io/docs/getting-started/installation/
 *   Windows: choco install k6
 * 
 * Run:
 *   k6 run loadtest/05-k6-load-test.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend, Counter } from 'k6/metrics';

// Custom metrics
const errorRate = new Rate('errors');
const responseTime = new Trend('response_time');
const requestsPerEndpoint = new Counter('requests_per_endpoint');

// Configuration
const BASE_URL = 'http://localhost:8000';

const endpoints = [
    { name: 'simple', path: '/test/simple', weight: 40 },
    { name: 'cpu', path: '/test/cpu', weight: 20 },
    { name: 'memory', path: '/test/memory', weight: 15 },
    { name: 'database', path: '/test/database', weight: 10 },
    { name: 'cache', path: '/test/cache', weight: 10 },
    { name: 'mixed', path: '/test/mixed', weight: 5 },
];

// Test scenarios
export const options = {
    scenarios: {
        // Scenario 1: Constant load
        constant_load: {
            executor: 'constant-vus',
            vus: 10,
            duration: '1m',
            tags: { scenario: 'constant' },
        },
        
        // Scenario 2: Ramping load
        ramping_load: {
            executor: 'ramping-vus',
            startVUs: 0,
            stages: [
                { duration: '30s', target: 20 },  // Ramp up to 20 users
                { duration: '1m', target: 20 },   // Stay at 20 users
                { duration: '30s', target: 50 },  // Ramp up to 50 users
                { duration: '1m', target: 50 },   // Stay at 50 users
                { duration: '30s', target: 0 },   // Ramp down to 0
            ],
            startTime: '1m',
            tags: { scenario: 'ramping' },
        },
        
        // Scenario 3: Spike test
        spike_test: {
            executor: 'ramping-vus',
            startVUs: 0,
            stages: [
                { duration: '10s', target: 5 },   // Normal load
                { duration: '10s', target: 100 }, // Spike!
                { duration: '20s', target: 100 }, // Stay at spike
                { duration: '10s', target: 5 },   // Back to normal
            ],
            startTime: '4m',
            tags: { scenario: 'spike' },
        },
    },
    
    thresholds: {
        'http_req_duration': ['p(95)<2000', 'p(99)<5000'], // 95% < 2s, 99% < 5s
        'http_req_failed': ['rate<0.1'],  // Error rate < 10%
        'errors': ['rate<0.1'],
    },
};

// Select weighted random endpoint
function getRandomEndpoint() {
    const totalWeight = endpoints.reduce((sum, e) => sum + e.weight, 0);
    let random = Math.random() * totalWeight;
    
    for (const endpoint of endpoints) {
        random -= endpoint.weight;
        if (random <= 0) {
            return endpoint;
        }
    }
    
    return endpoints[0];
}

export default function () {
    const endpoint = getRandomEndpoint();
    const url = `${BASE_URL}${endpoint.path}`;
    
    const params = {
        headers: {
            'User-Agent': 'k6-load-test',
        },
        tags: {
            endpoint: endpoint.name,
        },
    };
    
    const startTime = Date.now();
    const response = http.get(url, params);
    const duration = Date.now() - startTime;
    
    // Record metrics
    responseTime.add(duration);
    requestsPerEndpoint.add(1, { endpoint: endpoint.name });
    
    // Checks
    const checkResult = check(response, {
        'status is 200': (r) => r.status === 200,
        'response time < 5000ms': (r) => r.timings.duration < 5000,
        'has json response': (r) => {
            try {
                JSON.parse(r.body);
                return true;
            } catch {
                return false;
            }
        },
    });
    
    errorRate.add(!checkResult);
    
    // Think time (simulate real user behavior)
    sleep(Math.random() * 2 + 0.5); // 0.5-2.5 seconds
}

export function handleSummary(data) {
    console.log('\n' + '='.repeat(60));
    console.log('📊 K6 Load Test Summary');
    console.log('='.repeat(60));
    console.log(`
Total Requests:     ${data.metrics.http_reqs.values.count}
Request Rate:       ${data.metrics.http_reqs.values.rate.toFixed(2)}/s
Failed Requests:    ${data.metrics.http_req_failed.values.passes} (${(data.metrics.http_req_failed.values.rate * 100).toFixed(2)}%)

Response Time:
  Min:              ${data.metrics.http_req_duration.values.min.toFixed(2)}ms
  Avg:              ${data.metrics.http_req_duration.values.avg.toFixed(2)}ms
  Med:              ${data.metrics.http_req_duration.values.med.toFixed(2)}ms
  P90:              ${data.metrics.http_req_duration.values['p(90)'].toFixed(2)}ms
  P95:              ${data.metrics.http_req_duration.values['p(95)'].toFixed(2)}ms
  P99:              ${data.metrics.http_req_duration.values['p(99)'].toFixed(2)}ms
  Max:              ${data.metrics.http_req_duration.values.max.toFixed(2)}ms

Data Transfer:
  Sent:             ${(data.metrics.data_sent.values.count / 1024 / 1024).toFixed(2)} MB
  Received:         ${(data.metrics.data_received.values.count / 1024 / 1024).toFixed(2)} MB
`);
    console.log('='.repeat(60));
    console.log('✅ Check Grafana Dashboard:');
    console.log('   http://localhost:3000');
    console.log('');
    console.log('   Observe:');
    console.log('   - CPU usage during different scenarios');
    console.log('   - Memory usage patterns');
    console.log('   - Network I/O throughput');
    console.log('   - Laravel response time percentiles');
    console.log('   - HTTP request rate variations');
    console.log('='.repeat(60));
    
    return {
        'stdout': '', // Don't print default summary
    };
}
