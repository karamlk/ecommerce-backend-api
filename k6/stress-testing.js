import http from 'k6/http';
import { check } from 'k6';
import { Counter, Rate } from 'k6/metrics';

// Task 9
const orderSuccess = new Counter('order_success');
const orderFail = new Counter('order_fail');
const errorRate = new Rate('error_rate');

export const options = {
    scenarios: {
        concurrent_checkout: {
            executor: 'per-vu-iterations',
            vus: 100,
            iterations: 1,
            maxDuration: '2m',
        },
    },

    thresholds: {
        http_req_duration: ['p(95)<25000'],
        error_rate: ['rate<0.10'],
        checks: ['rate>0.90'],
    },
};

export default function () {

    const userId = __VU;
    const email = `stressuser${userId}@test.com`;

    // Login
    const loginRes = http.post(
        'http://otp-lar.local/api/login',
        JSON.stringify({
            identifier: email,
            password: 'password123',
        }),
        {
            headers: {
                'Content-Type': 'application/json',
            },
            tags: { name: 'login' },
        }
    );

    const loginOk = check(loginRes, {
        'login status 200': (r) => r.status === 200,
        'has token': (r) => r.status === 200 && r.json('token'),
    });

    if (!loginOk) {
        errorRate.add(1);
        orderFail.add(1);
        return;
    }

    const token = loginRes.json('token');

    const headers = {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
    };

    // Browse products
    const homeRes = http.get(
        'http://otp-lar.local/api/home/products',
        {
            headers,
            tags: { name: 'home_products' },
        }
    );

    check(homeRes, {
        'home products 200': (r) => r.status === 200,
    });

    // Submit order
    const orderRes = http.post(
        'http://otp-lar.local/api/orders',
        null,
        {
            headers,
            timeout: '5s',
            tags: { name: 'place_order' },
        }
    );

    const orderOk = check(orderRes, {
        'order accepted': (r) => r.status === 202,
        'order not 500': (r) => r.status !== 500,
    });

    if (orderOk) {
        orderSuccess.add(1);
        errorRate.add(0);
    } else {
        orderFail.add(1);
        errorRate.add(1);
    }
}

// import http from 'k6/http';
// import { check, sleep } from 'k6';
// import { Counter, Rate, Trend } from 'k6/metrics';

// const orderSuccess  = new Counter('order_success');
// const orderFail     = new Counter('order_fail');
// const errorRate     = new Rate('error_rate');
// const orderDuration = new Trend('order_duration_ms');

// export const options = {
//     scenarios: {
//         stress_test: {
//             executor: 'ramping-vus',
//             startVUs: 0,
//             stages: [
//                 { duration: '30s', target: 50  },
//                 { duration: '60s', target: 100 },
//                 { duration: '60s', target: 100 },
//                 { duration: '30s', target: 0   },
//             ],
//         },
//     },

//     thresholds: {
//         'http_req_duration': ['p(95)<40000'], // honest local machine limit
//         'error_rate':        ['rate<0.05'],
//         'checks':            ['rate>0.90'],
//     },
// };

// // Login once per VU — Gemini's improvement
// let token   = null;
// let headers = null;

// export default function () {

//     const userId = (__VU % 100) + 1;
//     const email  = `stressuser${userId}@test.com`;
//     const BASE_URL = 'http://otp-lar.local/api';

//     // ── Step 1: Login once per VU ─────────────────────────────
//     if (!token) {
//         const loginRes = http.post(
//             `${BASE_URL}/login`,
//             JSON.stringify({
//                 identifier:    email,      // ← correct field name
//                 password: 'password123',
//             }),
//             {
//                 headers: { 'Content-Type': 'application/json' },
//                 tags:    { name: 'login' },
//             }
//         );

//         const loginOk = check(loginRes, {
//             'login status 200': (r) => r.status === 200,
//             'has token':        (r) => r.status === 200 && r.json('token'),
//         });

//         if (!loginOk) {
//             errorRate.add(1);
//             orderFail.add(1);
//             sleep(1);
//             return;
//         }

//         token   = loginRes.json('token');
//         headers = {
//             'Content-Type':  'application/json',
//             'Authorization': `Bearer ${token}`,
//         };

//         sleep(0.5);
//     }

//     // ── Step 2: Browse home products ──────────────────────────
//     const homeRes = http.get(
//         `${BASE_URL}/home/products`,
//         { headers, tags: { name: 'home_products' } }
//     );

//     check(homeRes, {
//         'home products 200': (r) => r.status === 200,
//     });

//     sleep(0.5);

//     // ── Step 3: Place order ───────────────────────────────────
//     const start    = Date.now();
//     const orderRes = http.post(
//         `${BASE_URL}/orders`,
//         null,
//         {
//             headers,
//             tags:    { name: 'place_order' },
//             timeout: '60s',
//         }
//     );
//     const duration = Date.now() - start;

//     orderDuration.add(duration);

//     check(orderRes, {
//         'order no server error': (r) => r.status !== 500,
//        'order accepted': (r) => r.status === 202 || r.status === 400,
//     });

//     if (orderRes.status === 202) {
//         orderSuccess.add(1);
//         errorRate.add(0);
//         // ── Step 4: Refill cart — Gemini's improvement ────────
//         // Without this, cart is empty after first order
//         // and all subsequent iterations return 400
//         const cartItems = http.get(
//             `${BASE_URL}/cart`,
//             { headers }
//         ).json('data');

//         if (!cartItems || cartItems.length === 0) {
//             // Get a product to add — use first available
//             const products = http.get(
//                 `${BASE_URL}/home/products`,
//                 { headers }
//             ).json('data');

//             if (products && products.length > 0) {
//                 const product = products[(userId - 1) % products.length];
//                 http.post(
//                     `${BASE_URL}/cart`,
//                     JSON.stringify({
//                         product_id: product.id,
//                         quantity:   1,
//                     }),
//                     { headers, tags: { name: 'refill_cart' } }
//                 );
//             }
//         }

//     } else if (orderRes.status === 400) {
//         errorRate.add(0);
//     } else {
//         orderFail.add(1);
//         errorRate.add(1);
//     }

//     sleep(1.5);
// }

///////////////////////////////////////////////////////////////////////////////////////////
// import http from 'k6/http';
// import { check, sleep } from 'k6';
// import { Counter, Rate, Trend } from 'k6/metrics';

// // ── Custom metrics ────────────────────────────────────────────
// const orderSuccess = new Counter('order_success');
// const orderFail = new Counter('order_fail');
// const errorRate = new Rate('error_rate');
// const orderDuration = new Trend('order_duration_ms');

// // ── Test configuration ────────────────────────────────────────
// export const options = {
//     scenarios: {
//         stress_test: {
//             executor: 'ramping-vus',
//             startVUs: 0,
//             stages: [
//                 { duration: '30s', target: 50 },  // Ramp up to 50 users
//                 { duration: '60s', target: 100 }, // Ramp up to 100 users
//                 { duration: '60s', target: 100 }, // Sustained peak load at 100 users
//                 { duration: '30s', target: 0 },   // Safe ramp down
//             ],
//         },
//     },

//     thresholds: {
//         // 95% of requests must complete under 3.5s (optimized for shared local windows hardwares)
//         'http_req_duration': ['p(95)<3500'], 
//         // Real exceptions and system drops (500s) must stay below 5%
//         'error_rate': ['rate<0.05'],
//         // 90% of all code checks must pass
//         'checks': ['rate>0.90'],
//     },
// };

// // ── Shared execution state across VU loops ────────────────────
// // These variables persist for each unique Virtual User across its iterations
// let token = null;
// let headers = null;

// export default function () {
//     // Each VU maps cleanly to one of your 100 pre-seeded database stress users
//     const userId = (__VU % 100) + 1;
//     const email = `stressuser${userId}@test.com`;
//     const BASE_URL = 'http://otp-lar.local/api';

//     // ── Step 1: Token Handshake (Runs ONCE per Virtual User) ──
//     if (!token) {
//         const loginRes = http.post(
//             `${BASE_URL}/login`,
//             JSON.stringify({
//                 identifier: email,
//                 password: 'password123',
//             }),
//             {
//                 headers: { 'Content-Type': 'application/json' },
//                 tags: { name: 'login' },
//             }
//         );

//         const loginOk = check(loginRes, {
//             'login status 200': (r) => r.status === 200,
//             'has token': (r) => r.status === 200 && r.body && r.json('token'),
//         });

//         if (!loginOk) {
//             errorRate.add(1);
//             orderFail.add(1);
//             // Wait before retrying to prevent pinning the CPU
//             sleep(1); 
//             return;
//         }

//         token = loginRes.json('token');
//         headers = {
//             'Content-Type': 'application/json',
//             'Authorization': `Bearer ${token}`,
//         };
        
//         // Pacing: think-time after authenticating
//         sleep(0.5); 
//     }

//     // ── Step 2: Browse Home Products (Redis Cache Load) ──────
//     const homeRes = http.get(
//         `${BASE_URL}/home/products`,
//         {
//             headers,
//             tags: { name: 'home_products' },
//         }
//     );

//     check(homeRes, {
//         'home products 200': (r) => r.status === 200,
//     });

//     sleep(0.5);

//     // ── Step 3: Place Order (Transaction & Lock Stress) ──────
//     const start = Date.now();
//     const orderRes = http.post(
//         `${BASE_URL}/orders`,
//         null,
//         {
//             headers,
//             tags: { name: 'place_order' },
//             timeout: '15s', // Higher timeout to handle local database locking queues
//         }
//     );
//     const duration = Date.now() - start;
//     orderDuration.add(duration);

//     const orderOk = check(orderRes, {
//         'order no server error': (r) => r.status !== 500,
//         'order 201 or 400': (r) => r.status === 201 || r.status === 400,
//     });

//     if (orderRes.status === 201) {
//         orderSuccess.add(1);
//         errorRate.add(0);

//         // ── Step 4: Refill Cart (The Loop Fix) ───────────────────
//         // Map the user dynamically back to one of the 10 products from your seeder distribution
//         const targetProductId = ((userId - 1) % 10) + 1; 
        
//         http.post(
//             `${BASE_URL}/cart`, 
//             JSON.stringify({
//                 product_id: targetProductId,
//                 quantity: 1
//             }), 
//             { headers, tags: { name: 'refill_cart' } }
//         );

//     } else if (orderRes.status === 400) {
//         // Expected behavior if cart was emptied in a race condition overlap
//         errorRate.add(0);
//     } else {
//         // Unexpected error (500 Internal Error, Timeout, etc.)
//         orderFail.add(1);
//         errorRate.add(1);
//     }

//     // Give your laptop's i5 processor a short breathing window before starting the loop again
//     sleep(1.5); 
// }


//******************************************************************* */


// import http from 'k6/http';
// import { check, sleep } from 'k6';
// import { Counter, Rate, Trend } from 'k6/metrics';

// // ── Custom metrics ────────────────────────────────────────────
// const orderSuccess = new Counter('order_success');
// const orderFail = new Counter('order_fail');
// const errorRate = new Rate('error_rate');
// const orderDuration = new Trend('order_duration_ms');

// // ── Test configuration ────────────────────────────────────────
// export const options = {
//     scenarios: {
//         stress_test: {
//             executor: 'ramping-vus',
//             startVUs: 0,
//             stages: [
//                 { duration: '30s', target: 50 }, // ramp up to 50 users
//                 { duration: '60s', target: 100 }, // ramp up to 100 users
//                 { duration: '60s', target: 100 }, // hold at 100 users
//                 { duration: '30s', target: 0 }, // ramp down
//             ],
//         },
//     },

//     thresholds: {
//         'http_req_duration': ['p(95)<20000'], 
//         // Error rate must stay below 10%
//         'error_rate': ['rate<0.10'],
//         // 90% of all checks must pass
//         'checks': ['rate>0.90'],
//     },
// };

// // ── Main test function — runs for each virtual user ───────────
// export default function () {

//     // Each VU maps to one of the 100 stress test users
//     const userId = (__VU % 100) + 1;
//     const email = `stressuser${userId}@test.com`;

//     // ── Step 1: Login ─────────────────────────────────────────
//     const loginRes = http.post(
//         'http://otp-lar.local/api/login',
//         JSON.stringify({
//             identifier: email,
//             password: 'password123',
//         }),
//         {
//             headers: { 'Content-Type': 'application/json' },
//             tags: { name: 'login' },
//         }
//     );

//     const loginOk = check(loginRes, {
//         'login status 200': (r) => r.status === 200,
//         'has token': (r) => r.status === 200 && r.body && r.json('token'),
//     });

//     if (!loginOk) {
//         errorRate.add(1);
//         orderFail.add(1);
//         return;
//     }

//     const token = loginRes.json('token');
//     const headers = {
//         'Content-Type': 'application/json',
//         'Authorization': `Bearer ${token}`,
//     };

//     sleep(0.5); // realistic think time between requests

//     // ── Step 2: Browse home products ──────────────────────────
//     // Tests Redis cache under concurrent load
//     const homeRes = http.get(
//         'http://otp-lar.local/api/home/products',
//         {
//             headers,
//             tags: { name: 'home_products' },
//         }
//     );

//     check(homeRes, {
//         'home products 200': (r) => r.status === 200,
//     });

//     sleep(0.5);

//     // ── Step 3: Place order ───────────────────────────────────
//     // Critical endpoint — tests:
//     // Task 1:  lockForUpdate
//     // Task 2:  global capacity lock
//     // Task 7:  distributed lock per product + version check
//     // Task 8:  ACID transaction
//     const start = Date.now();
//     const orderRes = http.post(
//         'http://otp-lar.local/api/orders',
//         null,
//         {
//             headers,
//             tags: { name: 'place_order' },
//             timeout: '10s',
//         }
//     );
//     const duration = Date.now() - start;

//     orderDuration.add(duration);

//     const orderOk = check(orderRes, {
//         'order no server error': (r) => r.status !== 500,
//         'order 202 or 400': (r) => r.status === 202 || r.status === 400,
//     });

//     if (orderRes.status === 202) {
//         // Successful order placement
//         orderSuccess.add(1);
//         errorRate.add(0);
//     } else if (orderRes.status === 400) {
//         // Cart empty — user already placed order this iteration
//         // Not an error — expected behavior
//         errorRate.add(0);
//     } else {
//         // Unexpected error — 500 or other
//         orderFail.add(1);
//         errorRate.add(1);
//     }

//     sleep(1);
// }
