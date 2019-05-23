declare module 'push.js' {

    const defaultPush: Push;
    export default defaultPush;

    class Push {
        Permission: PushPermission;

        create(title: string, params?: PushNotificationParams): Promise<PushNotification>

        close(tag: string): void;

        clear(): void;

        config(params: PushParams): void;
    }

    export interface PushNotificationParams {
        body?: string;
        icon?: string;
        link?: string;
        timeout?: number;
        tag?: string;
        requireInteraction?: boolean;
        vibrate?: boolean;
        silent?: boolean;
        onClick?: Function;
        onError?: Function;
    }

    export interface PushParams {
        serviceWorker?: string;
        fallback?: Function;
    }

    export interface PushPermission {
        DEFAULT: string;
        GRANTED: string;
        DENIED: string;

        request(onGranted?: Function, onDenied?: Function): void;

        has(): boolean;

        get(): string;
    }

    export interface PushNotification {
        close(): void;
    }
}
