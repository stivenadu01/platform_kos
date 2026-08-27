const API = (() => {

  async function request(url, method = 'GET', data = null, loading = true) {
    try {
      if (loading) {
        Alpine.store('ui').startLoading();
      }

      let options = {
        method: method,
        headers: {
          'Accept': 'application/json'
        }
      };

      // SMART BODY HANDLING
      if (data) {

        // 🔥 CASE 1: sudah FormData → langsung pakai
        if (data instanceof FormData) {
          options.body = data;
        }
        // 🔥 CASE 2: object biasa → JSON
        else if (typeof data === 'object') {
          options.headers['Content-Type'] = 'application/json';
          options.body = JSON.stringify(data);
        }

        // override method untuk PUT & DELETE (FormData only)
        if ((method === 'PUT' || method === 'DELETE') && options.body instanceof FormData) {
          options.body.append('_method', method);
          options.method = 'POST';
        }
      }

      const csrfToken = window.__CSRF_TOKEN__ || document.querySelector('meta[name=csrf-token]')?.content || '';

      if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method) && csrfToken) {
        options.headers['X-CSRF-Token'] = csrfToken;
      }

      const res = await fetch(BASE_URL + '/api' + url, options);
      const json = await res.json();

      if (!json.success) {
        throw new Error(json.message || 'Terjadi kesalahan');
      }

      if (json.message) {
        Alpine.store('ui').toast(json.message, 'success');
      }

      return json;

    } catch (err) {
      Alpine.store('ui').toast(err.message, 'error');
      throw err;
    } finally {
      if (loading) {
        Alpine.store('ui').stopLoading();
      }
    }
  }

  return {
    get: (url, loading = true) => request(url, 'GET', null, loading),
    post: (url, data, loading = true) => request(url, 'POST', data, loading),
    put: (url, data, loading = true) => request(url, 'PUT', data, loading),
    delete: (url, data, loading = true) => request(url, 'DELETE', data, loading),
  };

})();