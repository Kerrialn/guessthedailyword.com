import { registerVueControllerComponents } from '@symfony/ux-vue';
registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));

import './bootstrap.js';
import './styles/app.scss';
import 'bootstrap/dist/js/bootstrap.js'