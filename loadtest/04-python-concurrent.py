#!/usr/bin/env python3

"""
=========================================
Level 4: Python Concurrent Load Test
Using asyncio for concurrent requests
=========================================
"""

import asyncio
import aiohttp
import time
import random
from datetime import datetime
from collections import defaultdict

# Configuration
BASE_URL = "http://localhost:8000"
CONCURRENT_USERS = 20
TOTAL_REQUESTS = 500
TIMEOUT = 10

ENDPOINTS = [
    "/test/simple",
    "/test/cpu",
    "/test/memory",
    "/test/database",
    "/test/cache",
    "/test/mixed",
]

# Statistics
stats = {
    'total': 0,
    'success': 0,
    'failed': 0,
    'response_times': [],
    'status_codes': defaultdict(int),
    'endpoint_stats': defaultdict(lambda: {'count': 0, 'success': 0, 'total_time': 0})
}

async def make_request(session, semaphore, request_id):
    """Make a single HTTP request"""
    async with semaphore:
        endpoint = random.choice(ENDPOINTS)
        url = f"{BASE_URL}{endpoint}"
        
        start_time = time.time()
        try:
            async with session.get(url, timeout=aiohttp.ClientTimeout(total=TIMEOUT)) as response:
                await response.text()
                duration = (time.time() - start_time) * 1000  # ms
                
                stats['total'] += 1
                stats['response_times'].append(duration)
                stats['status_codes'][response.status] += 1
                stats['endpoint_stats'][endpoint]['count'] += 1
                stats['endpoint_stats'][endpoint]['total_time'] += duration
                
                if response.status == 200:
                    stats['success'] += 1
                    stats['endpoint_stats'][endpoint]['success'] += 1
                    print(f"✓ [{request_id:04d}] {endpoint:20s} - {response.status} - {duration:6.0f}ms")
                else:
                    stats['failed'] += 1
                    print(f"✗ [{request_id:04d}] {endpoint:20s} - {response.status} - {duration:6.0f}ms")
                    
        except asyncio.TimeoutError:
            stats['total'] += 1
            stats['failed'] += 1
            print(f"✗ [{request_id:04d}] {endpoint:20s} - TIMEOUT")
        except Exception as e:
            stats['total'] += 1
            stats['failed'] += 1
            print(f"✗ [{request_id:04d}] {endpoint:20s} - ERROR: {str(e)[:50]}")

async def run_load_test():
    """Run the load test"""
    semaphore = asyncio.Semaphore(CONCURRENT_USERS)
    
    async with aiohttp.ClientSession() as session:
        tasks = []
        for i in range(TOTAL_REQUESTS):
            task = asyncio.create_task(make_request(session, semaphore, i + 1))
            tasks.append(task)
        
        await asyncio.gather(*tasks)

def print_statistics(duration):
    """Print test statistics"""
    print("\n" + "="*60)
    print("📊 Load Test Results")
    print("="*60)
    print(f"Total Requests:     {stats['total']}")
    print(f"Successful:         {stats['success']}")
    print(f"Failed:             {stats['failed']}")
    print(f"Duration:           {duration:.2f}s")
    print(f"Requests/sec:       {stats['total'] / duration:.2f}")
    print()
    
    if stats['response_times']:
        response_times = sorted(stats['response_times'])
        print("Response Time Statistics:")
        print(f"  Min:              {min(response_times):.0f}ms")
        print(f"  Max:              {max(response_times):.0f}ms")
        print(f"  Avg:              {sum(response_times) / len(response_times):.0f}ms")
        print(f"  Median:           {response_times[len(response_times)//2]:.0f}ms")
        print(f"  P95:              {response_times[int(len(response_times)*0.95)]:.0f}ms")
        print(f"  P99:              {response_times[int(len(response_times)*0.99)]:.0f}ms")
        print()
    
    print("Status Codes:")
    for code, count in sorted(stats['status_codes'].items()):
        print(f"  {code}:              {count} ({count/stats['total']*100:.1f}%)")
    print()
    
    print("Endpoint Statistics:")
    for endpoint, data in sorted(stats['endpoint_stats'].items()):
        if data['count'] > 0:
            avg_time = data['total_time'] / data['count']
            success_rate = (data['success'] / data['count']) * 100
            print(f"  {endpoint:20s} - {data['count']:3d} requests - "
                  f"avg: {avg_time:6.0f}ms - success: {success_rate:5.1f}%")
    print()

def main():
    """Main function"""
    print("🔥 Python Concurrent Load Test")
    print("="*60)
    print()
    print("Configuration:")
    print(f"  Base URL:           {BASE_URL}")
    print(f"  Concurrent Users:   {CONCURRENT_USERS}")
    print(f"  Total Requests:     {TOTAL_REQUESTS}")
    print(f"  Timeout:            {TIMEOUT}s")
    print()
    print("🚀 Starting load test...")
    print("   Monitor in Grafana: http://localhost:3000")
    print()
    print("-"*60)
    
    start_time = time.time()
    asyncio.run(run_load_test())
    duration = time.time() - start_time
    
    print_statistics(duration)
    
    print("="*60)
    print("✅ Check Grafana Dashboard:")
    print("   http://localhost:3000")
    print()
    print("   Metrics to observe:")
    print("   - CPU Usage spike")
    print("   - Memory consumption")
    print("   - Network I/O")
    print("   - Laravel response time (p50, p95)")
    print("   - HTTP requests/second")
    print("="*60)

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n⚠️  Test interrupted by user")
    except Exception as e:
        print(f"\n\n❌ Error: {e}")
