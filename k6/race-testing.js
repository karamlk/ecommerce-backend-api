import http from 'k6/http';
import { check } from 'k6';
import { SharedArray } from 'k6/data';

// 1. Credentials from your testSeed seeder
const users = new SharedArray('users', function () {
    return [
        { email: 'email@gmail.com', password: 'password' }, // User A
        // { email: 'b@test.com', password: 'password' }, // User B
    ];
});


const BASE_URL =  'http://otp-lar.local/api';

export const options = {
    scenarios: {
        race_condition_test: {
            executor: 'per-vu-iterations',
            vus: 2,          // Exactly 2 users to test the single-stock item
            iterations: 1,   
            maxDuration: '10s',
        },
    },
};

export default function () {
    const userData = users[__VU - 1]; 

    // A. Login to get Sanctum token
    const loginRes = http.post(`${BASE_URL}/login`, JSON.stringify({
        identifier: userData.email,
        password: userData.password,
    }), { headers: { 'Content-Type': 'application/json' } });

    const token = loginRes.json('token');
    const headers = {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
    };


    const orderRes = http.post(`${BASE_URL}/orders`, null, { headers });

    check(orderRes, {
        'status is 201': (r) => r.status === 201,
        'order': (r) => r.json('message') === 'order created',
    });
}
