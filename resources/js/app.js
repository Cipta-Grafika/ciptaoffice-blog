import 'bootstrap/dist/js/bootstrap.bundle';
import { initViteBfcacheRecovery } from './support/vite-bfcache';

initViteBfcacheRecovery();

const contextLoaders = {
    site: () => import('./contexts/site'),
    auth: () => import('./contexts/auth'),
    cms: () => import('./contexts/cms'),
};

const context = document.body?.dataset.appContext;
const loadContext = contextLoaders[context];

if (loadContext) {
    loadContext()
        .then(({ init }) => init())
        .catch((error) => console.error(`Gagal memuat context JavaScript "${context}".`, error));
}
