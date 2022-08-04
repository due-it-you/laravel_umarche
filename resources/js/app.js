import './bootstrap';

import Alpine from 'alpinejs';

import MicroModal from 'micromodal';
MicroModal.init({
    disableScroll : true
});

window.Alpine = Alpine;

Alpine.start();
