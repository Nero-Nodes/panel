import http from '@/api/http';

export interface DiscordResponse {
    complete: boolean;
    intended?: string;
}

export default (): Promise<DiscordResponse> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/login/discord')
            .then(response => {
                if (!(response.data instanceof Object)) {
                    return reject(new Error('Unable to process this Discord login.'));
                }
                return resolve({
                    complete: response.data.data.complete,
                    intended: response.data.data.intended || undefined,
                });
            })
            .catch(reject);
    });
};
