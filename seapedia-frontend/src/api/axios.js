import axios from 'axios'

/**
 * Instance axios terpusat untuk komunikasi ke backend Laravel SEAPEDIA.
 *
 * VITE_API_BASE_URL diatur lewat file .env di root project frontend,
 * contoh: VITE_API_BASE_URL=http://127.0.0.1:8000/api
 */
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api',
  headers: {
    Accept: 'application/json',
  },
})

// Sisipkan token Sanctum ke setiap request kalau sudah login
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('seapedia_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Kalau token expired/invalid (401), otomatis logout di sisi frontend
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('seapedia_token')
    }
    return Promise.reject(error)
  }
)

export default api
