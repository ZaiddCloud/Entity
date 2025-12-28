import { vi } from 'vitest';
import { config } from '@vue/test-utils';

// Mock Inertia router
vi.mock('@inertiajs/vue3', async () => {
    const actual = await vi.importActual('@inertiajs/vue3');
    return {
        ...actual,
        router: {
            get: vi.fn(),
            post: vi.fn(),
            put: vi.fn(),
            delete: vi.fn(),
            patch: vi.fn(),
            reload: vi.fn(),
        },
        Link: {
            template: '<a><slot /></a>',
        },
        Head: {
            template: '<div />',
        },
        usePage: () => ({
            props: {
                auth: {
                    user: {
                        name: 'Test User',
                        email: 'test@example.com',
                    },
                },
                ziggy: {
                    location: 'http://localhost',
                },
            },
        }),
    };
});

// Mock Ziggy route() function
const routeMock = vi.fn((name) => {
    if (!name) {
        return {
            current: vi.fn().mockReturnValue(false),
        };
    }
    return `http://localhost/${name}`;
});
routeMock.current = vi.fn().mockReturnValue(false);

// Make route available globally for Vue components
config.global.mocks = {
    route: routeMock,
};

// Also set as global for non-Vue code
vi.stubGlobal('route', routeMock);
global.route = routeMock;
window.route = routeMock;
