<template>
  <div class="hello">
    <h3>Vue组件请求接口示例</h3>
    <el-button type="primary">I am ElButton</el-button>
    <van-button type="primary" @click="getData">vant按钮：点击获取接口数据</van-button>
    <div v-if="result">
      {{ result }}
    </div>
    
  </div>
</template>

<script setup>
import { ref } from 'vue';
const result = ref('');

const getData = async () => {
  const res = await window.axios.get('/show/2');
  if (res.status === 200) {
    result.value = res.data;
  }
};
</script>

<style scoped>
.hello {
  padding: 15px;
  border: 1px solid #eee;
}
</style>