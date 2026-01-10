import './bootstrap';
// import '../css/app.css';
// Vue3标准引入方式
import { createApp } from 'vue';

// 引入你的组件
import HelloWorld from './components/HelloWorld.vue';

// 创建Vue实例 + 全局注册组件
const app = createApp({});
app.component('HelloWorld', HelloWorld);

// 挂载到 #app 容器
app.mount('#app');