import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Bootstrap JavaScript (必須在 AdminLTE 之前載入)
import * as bootstrap from 'bootstrap';

// AdminLTE JavaScript
import 'admin-lte/dist/js/adminlte.min.js';

// 讓 Bootstrap 在全域可用 (AdminLTE 需要)
window.bootstrap = bootstrap;
