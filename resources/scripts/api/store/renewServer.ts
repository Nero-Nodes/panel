import http from '@/api/http';

export default (uuid: string, current: number | null): Promise<any> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/store/renew/${uuid}`, {
            uuid, current,
        }).then((data) => {
            resolve(data.data || []);
        }).catch(reject);
    });
};
