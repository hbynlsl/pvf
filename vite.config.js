import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { VantResolver } from '@vant/auto-import-resolver'
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers'

export default defineConfig({
  plugins: [
    vue(),
    AutoImport({
      resolvers: [
        VantResolver(),
        ElementPlusResolver()
      ],
    }),
    Components({
      resolvers: [
        VantResolver(),
        ElementPlusResolver()
      ],
    }),
  ],
  resolve: {
    alias: {
      // 关键：将vue别名指向支持运行时编译的版本
      'vue': path.resolve(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js')
    }
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    open: false,
    cors: true,
    origin: 'http://localhost:5173' // 新增：指定Vite服务的源地址，关键！
  },
  build: {
    outDir: 'public/dist',
    assetsDir: '',
    copyPublicDir: false,
    // 关键：关闭文件名哈希
    rollupOptions: {
      input: path.resolve(__dirname, 'resources/js/app.js'),
      output: {
        // 配置JS/CSS/静态资源的固定文件名
        entryFileNames: 'app.js', // 入口JS文件名固定为app.js
        chunkFileNames: 'chunk.js', // 分包JS文件名（如果有分包的话）
        assetFileNames: '[name].[ext]' // 静态资源（如图片、字体）用原文件名
      }
    },
    // 关闭CSS文件名哈希（Vite 3+版本需要单独配置）
    cssCodeSplit: false, // 保持CSS分离（如果需要合并CSS，设为false）
    cssFileName: 'app.css' // CSS文件名固定为app.css
  },
  optimizeDeps: { // ✅ 新增核心配置：预构建裸模块，让浏览器能识别vue/axios
    include: ['vue', 'axios']
  }
})