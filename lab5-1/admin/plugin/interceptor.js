// plugin/interceptor.js
import { axiosClient } from './axios.js';
import { tokenHelper } from '../helpers/token.helper.js';

export const setupInterceptors = () => {
  console.log('Interceptors setup complete');
};

export { axiosClient };