import { Pipe, PipeTransform } from '@angular/core';

declare var _pk_translate : (v: string, args: string[]) => string;

@Pipe({name: 'translate'})
export class TranslatePipe implements PipeTransform {
    transform(key: string, ...args: string[]): string {
        return _pk_translate(key, args);
    }
}