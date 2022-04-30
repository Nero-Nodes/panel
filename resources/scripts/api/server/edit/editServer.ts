import http from '@/api/http';

export default (uuid: string, resource: number, value: number): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/servers/${uuid}/settings/edit`, { value, resource })
            .then(() => resolve())
            .catch(reject);
    });
};
