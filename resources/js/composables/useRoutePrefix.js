import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useRoutePrefix() {
    const page = usePage();
    return computed(() => {
        const url = page.url || '';
        return url.startsWith('/bpsdm') ? 'bpsdm' : 'admin';
    });
}
