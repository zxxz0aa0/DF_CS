import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Bootstrap JavaScript (必須在 AdminLTE 之前載入)
import * as bootstrap from 'bootstrap';

// 讓 Bootstrap 在全域可用（AdminLTE 會在 AdminLayout 動態載入）
window.bootstrap = bootstrap;
