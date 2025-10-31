"""
=========================================
Level 6: Locust Load Test (Production-Grade)
Distributed load testing with web UI
=========================================

Install:
    pip install locust

Run:
    # Basic mode
    locust -f loadtest/06-locust-test.py --host=http://localhost:8000

    # Headless mode
    locust -f loadtest/06-locust-test.py --host=http://localhost:8000 \
           --users 100 --spawn-rate 10 --run-time 5m --headless

    # Distributed mode (master)
    locust -f loadtest/06-locust-test.py --host=http://localhost:8000 --master

    # Distributed mode (worker)
    locust -f loadtest/06-locust-test.py --host=http://localhost:8000 --worker

Web UI:
    http://localhost:8089
"""

from locust import HttpUser, task, between, events
from locust.runners import MasterRunner
import random
import time
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class LaravelUser(HttpUser):
    """
    Simulates a user interacting with Laravel application
    """
    
    # Wait time between tasks (simulates real user behavior)
    wait_time = between(1, 3)  # 1-3 seconds
    
    def on_start(self):
        """Called when a user starts"""
        logger.info(f"User {self.environment.runner.user_count} started")
    
    @task(40)  # Weight: 40% of requests
    def simple_endpoint(self):
        """Test simple endpoint - lowest load"""
        with self.client.get("/test/simple", catch_response=True) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Got status code {response.status_code}")
    
    @task(20)  # Weight: 20% of requests
    def cpu_intensive(self):
        """Test CPU intensive endpoint"""
        with self.client.get("/test/cpu", catch_response=True) as response:
            if response.status_code == 200:
                try:
                    data = response.json()
                    if data.get('status') == 'ok':
                        response.success()
                    else:
                        response.failure("Invalid response data")
                except Exception as e:
                    response.failure(f"JSON parse error: {e}")
            else:
                response.failure(f"Got status code {response.status_code}")
    
    @task(15)  # Weight: 15% of requests
    def memory_intensive(self):
        """Test memory intensive endpoint"""
        with self.client.get("/test/memory", catch_response=True) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Got status code {response.status_code}")
    
    @task(10)  # Weight: 10% of requests
    def database_query(self):
        """Test database endpoint"""
        with self.client.get("/test/database", catch_response=True) as response:
            if response.status_code == 200:
                response.success()
            elif response.status_code == 500:
                response.failure("Database error")
            else:
                response.failure(f"Got status code {response.status_code}")
    
    @task(10)  # Weight: 10% of requests
    def cache_operations(self):
        """Test cache endpoint"""
        with self.client.get("/test/cache", catch_response=True) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Got status code {response.status_code}")
    
    @task(5)  # Weight: 5% of requests
    def mixed_operations(self):
        """Test mixed endpoint - combination of operations"""
        with self.client.get("/test/mixed", catch_response=True) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Got status code {response.status_code}")
    
    @task(3)  # Weight: 3% of requests - occasional slow requests
    def slow_endpoint(self):
        """Test slow endpoint - simulates slow operations"""
        with self.client.get("/test/slow", catch_response=True, timeout=10) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Got status code {response.status_code}")


class AdminUser(HttpUser):
    """
    Simulates admin users checking metrics
    Less frequent but important for monitoring
    """
    
    wait_time = between(5, 15)  # Less frequent requests
    weight = 1  # Lower weight compared to regular users
    
    @task
    def check_metrics(self):
        """Admin checking Prometheus metrics"""
        with self.client.get("/metrics", catch_response=True) as response:
            if response.status_code == 200:
                if "laravel_app" in response.text:
                    response.success()
                else:
                    response.failure("Metrics not found in response")
            else:
                response.failure(f"Got status code {response.status_code}")


# Event listeners for custom statistics
@events.test_start.add_listener
def on_test_start(environment, **kwargs):
    """Called when test starts"""
    logger.info("=" * 60)
    logger.info("🔥 Locust Load Test Starting")
    logger.info("=" * 60)
    logger.info(f"Host: {environment.host}")
    logger.info("")
    logger.info("📊 Monitor in real-time:")
    logger.info("   Locust UI:    http://localhost:8089")
    logger.info("   Grafana:      http://localhost:3000")
    logger.info("")
    logger.info("   Observe in Grafana:")
    logger.info("   - CPU usage patterns")
    logger.info("   - Memory consumption")
    logger.info("   - Network I/O")
    logger.info("   - Disk I/O")
    logger.info("   - Laravel response time (p50, p95, p99)")
    logger.info("   - HTTP request rate")
    logger.info("   - Container metrics")
    logger.info("=" * 60)


@events.test_stop.add_listener
def on_test_stop(environment, **kwargs):
    """Called when test stops"""
    logger.info("")
    logger.info("=" * 60)
    logger.info("✅ Load Test Complete!")
    logger.info("=" * 60)
    logger.info("")
    logger.info("📊 Check detailed results:")
    logger.info("   Locust UI:    http://localhost:8089")
    logger.info("   Grafana:      http://localhost:3000")
    logger.info("=" * 60)


@events.request.add_listener
def on_request(request_type, name, response_time, response_length, exception, **kwargs):
    """Called on every request - can be used for custom metrics"""
    if exception:
        logger.warning(f"Request failed: {name} - {exception}")


# Custom load shapes (optional - advanced usage)
from locust import LoadTestShape

class StepLoadShape(LoadTestShape):
    """
    A step load shape that increases users in steps
    
    Usage:
        locust -f loadtest/06-locust-test.py --host=http://localhost:8000 \
               --headless --users 0 --spawn-rate 10
    """
    
    step_time = 30  # seconds per step
    step_load = 10  # users per step
    spawn_rate = 10
    time_limit = 300  # total test time in seconds
    
    def tick(self):
        run_time = self.get_run_time()
        
        if run_time > self.time_limit:
            return None
        
        current_step = int(run_time // self.step_time)
        return (current_step + 1) * self.step_load, self.spawn_rate


class SpikeLoadShape(LoadTestShape):
    """
    A spike load shape for stress testing
    
    Simulates sudden traffic spikes
    """
    
    def tick(self):
        run_time = self.get_run_time()
        
        if run_time < 60:
            # Normal load: 10 users
            return 10, 5
        elif run_time < 90:
            # Spike: 100 users
            return 100, 50
        elif run_time < 150:
            # Stay at spike
            return 100, 50
        elif run_time < 180:
            # Back to normal
            return 10, 10
        else:
            # End test
            return None


# Uncomment one of these to use custom load shape
# Comment out the class definition if not using
# class UserLoadShape(StepLoadShape):
#     pass

# class UserLoadShape(SpikeLoadShape):
#     pass
