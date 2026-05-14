// auth.service.js
import { axiosClient } from './plugin/axios.js';
import { tokenHelper } from './helpers/token.helper.js';
import { authHelper } from './helpers/auth.helper.js';

class AuthService {
  async register(email, password) {
    try {
      const response = await axiosClient.post('/auth/register', { email, password });
      if (response.success) {
        tokenHelper.setTokens(response.data.accessToken, response.data.refreshToken);
      }
      return response;
    } catch (error) {
      console.error('Registration error:', error);
      throw error;
    }
  }

  async login(email, password) {
    try {
      const response = await axiosClient.post('/auth/login', { email, password });
      if (response.success) {
        tokenHelper.setTokens(response.data.accessToken, response.data.refreshToken);
      }
      return response;
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  }

  async logout() {
    const user = authHelper.getCurrentUser();
    if (user) {
      try {
        await axiosClient.post('/auth/logout', { userId: user.id });
      } catch (error) {
        console.error('Logout error:', error);
      }
    }
    authHelper.logout();
  }

  isAuthenticated() {
    return authHelper.isAuthenticated();
  }

  getCurrentUser() {
    return authHelper.getCurrentUser();
  }
}

export const authService = new AuthService();