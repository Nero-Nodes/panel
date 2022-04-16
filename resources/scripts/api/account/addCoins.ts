import http from '@/api/http';

export default (rate: number): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/store/earn', { rate })
            .then(() => resolve())
            .catch(reject);
    });
};
