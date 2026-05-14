// helpers/auth.helper.js
import { tokenHelper } from './token.helper.js';

export const authHelper = {
  isAuthenticated() {
    return tokenHelper.hasTokens() && tokenHelper.isAccessTokenValid();
  },

  getCurrentUser() {
    const tokenData = tokenHelper.getTokenData();
    if (tokenData) {
      return {
        id: tokenData.sub,
        email: tokenData.email
      };
    }
    console.log('123')
    return null;
  },

  logout() {
    tokenHelper.clearTokens();
    window.location.href = '/login.html';
  },

  handleAuthError(error) {
    if (error.status === 401) {
      this.logout();
    }
  }
};