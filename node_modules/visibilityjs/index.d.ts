declare module 'visibilityjs' {
    export function every(interval: number, callback: Function): number;
    export function every(interval: number, hiddenInterval: number, callback: Function): number;
    export function onVisible(callback: Function): number|boolean;
    export function afterPrerendering(callback: Function): number|boolean;
    export function isSupported(): boolean;
    export function state(): string;
    export function hidden(): boolean;
    export function unbind(id: number);
    export function change(listener: VisiblityChangeListener): number|boolean;
    export function stop(id: number): boolean;

    type VisiblityChangeListener = (event: Event, state: string) => void;
}
