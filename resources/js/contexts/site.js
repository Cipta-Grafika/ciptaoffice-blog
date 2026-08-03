import { initArticleToc } from '../components/site/article-toc';
import { initMetricStrip } from '../components/site/metric-strip';
import { initLiveSearch } from '../components/site/live-search';
import { initRevealElements } from '../components/site/reveal';
import { initSiteHeader } from '../components/site/site-header';

const initializers = [
    initSiteHeader,
    initMetricStrip,
    initRevealElements,
    initArticleToc,
    initLiveSearch,
];

export function init() {
    initializers.forEach((initialize) => initialize());
}
