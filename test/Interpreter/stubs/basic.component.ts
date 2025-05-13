import { ChangeDetectionStrategy, Component, } from '@angular/core';
import { Foo } from 'bar';
import { Bar } from 'foo';

@Component({
    selector: 'test',
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        {{ 'fooBar.foo.' + bar | translate }}
    `,
})

export class Test {
    @Foo()
    bar: 'biz' | 'fiz' | null = null;

    private biz: number;

    protected foo(
        a: string,
        c?: 'foo' | 'bar' = 'foo',
        @Bar fiz: any = 1,
    ): void {
        const fiz = {

        };
    }
}
