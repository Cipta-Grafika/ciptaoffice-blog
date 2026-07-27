import { initCmsBackButtons } from '../components/cms/global/back-button';
import { initCmsNavigationSearch } from '../components/cms/global/navigation-search';
import { initCmsSidebar } from '../components/cms/global/sidebar';

const globalInitializers = [
    initCmsSidebar,
    initCmsBackButtons,
    initCmsNavigationSearch,
];

const pageLoaders = {
    'posts-form': () => import('../components/cms/pages/posts/form'),
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
