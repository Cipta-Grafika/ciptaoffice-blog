import { initArticleToc } from '../components/site/article-toc';
import { initMetricStrip } from '../components/site/metric-strip';
import { initProductSearch } from '../components/site/product-search';
import { initRevealElements } from '../components/site/reveal';
import { initSiteHeader } from '../components/site/site-header';

const initializers = [
    initSiteHeader,
    initMetricStrip,
    initRevealElements,
    initArticleToc,
    initProductSearch,
];

export function init() {
    initializers.forEach((initialize) => initialize());
}
