import http from '@/api/http';
import { AxiosResponse } from "axios";

export default (): Promise<any> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/discord').then((data: AxiosResponse) => resolve(data.data || [])).catch(reject);
    });
};
