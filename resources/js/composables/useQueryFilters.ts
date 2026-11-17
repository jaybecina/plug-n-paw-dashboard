import { router, usePage } from '@inertiajs/vue3';

/**
 * Update the page's query string (filters) and reload only this page's
 * props, keeping scroll position and resetting pagination.
 */
export function useQueryFilters() {
    const page = usePage();

    function apply(changes: Record<string, string | null>) {
        const url = new URL(page.url, window.location.origin);

        for (const [key, value] of Object.entries(changes)) {
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        }

        url.searchParams.delete('page');

        router.get(
            url.pathname + url.search,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    }

    function currentQuery(): Record<string, string> {
        return Object.fromEntries(
            new URL(page.url, window.location.origin).searchParams,
        );
    }

    return { apply, currentQuery };
}
