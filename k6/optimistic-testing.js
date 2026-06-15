import http from 'k6/http';
import { check } from 'k6';

export const options = {
    vus: 2,          
    iterations: 2,  
};

export default function () {
    const url = 'http://otp-lar.local/dev/update-product-before/1';

    const userEmail = (__VU === 1) ? 'admin@ecom.com' : 'admin1@ecom.com';
    const password = 'password'; 
    const priceUpdate = (__VU === 1) ? 150 : 200;

    const credentials = `${userEmail}:${password}`;
    const encodedCredentials = b64encode(credentials);

    const payload = JSON.stringify({
        price: priceUpdate,
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Basic ${encodedCredentials}`,
        },
    };

    const res = http.post(url, payload, params);

    console.log(`VU ${__VU} (${userEmail}) sent price: ${priceUpdate}. Status: ${res.status}`);

    check(res, {
        'is status 200': (r) => r.status === 200,
    });
}

function b64encode(str) {
    return Buffer.from(str).toString('base64');
}

