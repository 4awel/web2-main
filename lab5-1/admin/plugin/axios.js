// plugin/axios.js
import { tokenHelper } from '../helpers/token.helper.js';
import { authHelper } from '../helpers/auth.helper.js';

class AxiosClient {
  constructor() {
    this.baseURL = 'http://localhost:3000';
    this.isRefreshing = false;
    this.failedQueue = [];
  }

  processQueue(error, token = null) {
    this.failedQueue.forEach(promise => {
      if (error) {
        promise.reject(error);
      } else {
        promise.resolve(token);
      }
    });
    this.failedQueue = [];
  }

  async refreshToken() {
    const refreshToken = tokenHelper.getRefreshToken();
    if (!refreshToken) {
      throw new Error('No refresh token');
    }

    const response = await fetch(`${this.baseURL}/auth/refresh`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ refreshToken })
    });

    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.error);
    }

    const { accessToken, refreshToken: newRefreshToken } = data.data;
    tokenHelper.setTokens(accessToken, newRefreshToken);
    
    return accessToken;
  }

  async request(config) {
    const makeRequest = async (token) => {
      const headers = {
        'Content-Type': 'application/json',
        ...config.headers
      };

      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const response = await fetch(`${this.baseURL}${config.url}`, {
        method: config.method || 'GET',
        headers,
        body: config.body ? JSON.stringify(config.body) : undefined
      });

      const data = await response.json();

      if (!response.ok) {
        const error = new Error(data.error || 'Request failed');
        error.status = response.status;
        error.data = data;
        throw error;
      }

      return data;
    };

    try {
      const accessToken = tokenHelper.getAccessToken();
      return await makeRequest(accessToken);
    } catch (error) {
      if (error.status === 401 && !config._retry) {
        config._retry = true;

        if (this.isRefreshing) {
          return new Promise((resolve, reject) => {
            this.failedQueue.push({ resolve, reject });
          }).then(token => {
            return makeRequest(token);
          }).catch(err => {
            throw err;
          });
        }

        this.isRefreshing = true;

        try {
          const newToken = await this.refreshToken();
          this.processQueue(null, newToken);
          return await makeRequest(newToken);
        } catch (refreshError) {
          this.processQueue(refreshError, null);
          authHelper.logout();
          throw refreshError;
        } finally {
          this.isRefreshing = false;
        }
      }
      throw error;
    }
  }

  get(url, headers = {}) {
    return this.request({ url, method: 'GET', headers });
  }

  post(url, body, headers = {}) {
    return this.request({ url, method: 'POST', body, headers });
  }

  put(url, body, headers = {}) {
    return this.request({ url, method: 'PUT', body, headers });
  }

  delete(url, headers = {}) {
    return this.request({ url, method: 'DELETE', headers });
  }
}

export const axiosClient = new AxiosClient();