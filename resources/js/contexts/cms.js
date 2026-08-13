import { initCmsBackButtons } from '../components/cms/global/back-button';
import { initCmsConfirmModal } from '../components/cms/global/confirm-modal';
import { initCmsNavigationSearch } from '../components/cms/global/navigation-search';
import { initCmsSidebar } from '../components/cms/global/sidebar';
import { initCmsAjaxTable } from '../components/cms/global/ajax-table';
import { initImageDropzones } from '../components/cms/shared/image-dropzone';
import { initPasswordToggles } from '../components/auth/password-toggle';

const globalInitializers = [
    initCmsSidebar,
    initCmsBackButtons,
    initCmsConfirmModal,
    initCmsNavigationSearch,
    initCmsAjaxTable,
    initImageDropzones,
    initPasswordToggles,
];

const pageLoaders = {
    'posts-form': () => import('../components/cms/pages/posts/form'),
    'products-form': () => import('../components/cms/pages/products/form'),
};

export async function init() {
    globalInitializers.forEach((initialize) => initialize());

    const page = document.body?.dataset.cmsPage;
    const loadPage = pageLoaders[page];

    if (loadPage) {
        const { init: initPage } = await loadPage();
        await initPage();
    }
}
