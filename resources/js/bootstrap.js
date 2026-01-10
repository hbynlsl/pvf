// 引入axios
import axios from 'axios';
// 挂载到window，全局可用，无需在组件中重复引入
window.axios = axios;

// 配置axios请求头，和Laravel完全一致
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// 自动获取页面的CSRF令牌，注入到请求头（核心！解决419报错）
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}