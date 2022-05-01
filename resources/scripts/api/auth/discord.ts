import http from '@/api/http';

export default (): Promise<any> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/discord')
            .then((data) => resolve(data.data || []))
            .catch(reject);
    });
};
