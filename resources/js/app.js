import './bootstrap';
import './admin/pricing/pricing-hub';
import './admin/pricing/pricing-manager';
import { homeCategories } from './home/categories';
import { homePopularProducts } from './home/popular-products';
import { homePopularProductsV2 } from './home/popular-products-v2';
import { printairFooterHub } from './home/footer-hub';
import { typingText } from './ui/typingText';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.homeCategories = homeCategories;
window.homePopularProducts = homePopularProducts;
window.homePopularProductsV2 = homePopularProductsV2;
window.printairFooterHub = printairFooterHub;
window.typingText = typingText;

Alpine.start();
