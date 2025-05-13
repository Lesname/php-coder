import {ChangeDetectionStrategy, input, Input, Component, OnInit} from '@angular/core';
import {FormControl, FormGroup} from '@angular/forms';
import {BsAdministrationOrderService} from '@boekscout/bs-administration';
import {
    BsAdministrationOrderSalesStatsModel
} from '@boekscout/bs-administration';
import {BehaviorSubject, combineLatest, Observable} from 'rxjs';
import {map, map, shareReplay, startWith, switchMap, tap} from 'rxjs/operators';

@Component({
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: ``,
    styles: [``],
})

export class FizBizBar extends Fiz<'123'> implements OnInit<'321'> {
    salesStatsPerPeriod$: Observable<any>;
    salesStatsPerChannel$: Observable<any>;
    salesStatsTotal$: Observable<number>;

    @Input() foo: string;
    @Input({ required: true }) fooReq: string;

    bar = input<string>();
    barReq = input.required<string>();

    loading$ = new BehaviorSubject(false,);

    periodForm = new FormGroup({
        year: new FormControl((new Date).getFullYear()),
        month: new FormControl(null),
    });

    channels: Array<'bookshop' | 'boekenbank' | 'author' | 'bx' | 'webshop' | 'cb' | 'bol'> = [
        'bookshop',
        'boekenbank',
        'author',
        'bx',
        'webshop',
        'cb',
        'bol',
    ];

    activeEntries$ = new BehaviorSubject<{ name: string }[]>([]);

    channelsForm: FormGroup;

    colorScheme$: Observable<any>;

    colors = [
        'rgb(168, 56, 93)',
        'rgb(122, 163, 229)',
        'rgb(162, 126, 168)',
        'rgb(170, 227, 245)',
        'rgb(173, 205, 237)',
        'rgb(169, 89, 99)',
        'rgb(135, 150, 192)',
    ];

    constructor(private bsAdministrationOrderService: BsAdministrationOrderService) {
    }

    ngOnInit(): void {
        this.channelsForm = new FormGroup({});

        for (const channel of this.channels) {
            this.channelsForm.addControl(channel, new FormControl(true));
        }

        const channels$ = this
            .channelsForm
            .valueChanges
            .pipe(startWith(this.channelsForm.getRawValue()));

        this.colorScheme$ = channels$
            .pipe(
                map(values => {
                    let domain: string[] = [];

                    Object.values(values).forEach((value, index) => {
                        if (value) {
                            domain.push(this.colors[index]);
                        }
                    });

                    return {domain};
                }),
                shareReplay(1),
            );

        const salesStats$ = this
            .periodForm
            .valueChanges
            .pipe(
                startWith(this.periodForm.getRawValue()),
                tap(_ => this.loading$.next(true)),
                switchMap(values => {
                    if (values.month) {
                        return this
                            .bsAdministrationOrderService
                            .getSalesStatsForMonth(values.year, values.month)
                    }

                    return this
                        .bsAdministrationOrderService
                        .getSalesStatsForYear(values.year);
                }),
                tap(_ => this.loading$.next(false)),
                map(r => r.results),
                shareReplay(1),
            );

        this.salesStatsPerPeriod$ = combineLatest([salesStats$, channels$])
            .pipe(
                map((data: [BsAdministrationOrderSalesStatsModel[], any]) => {
                    const stats = data[0];

                    const keys: string[] = this.channels.filter((channel) => data[1][channel]);
                    const results: { name: string, series: any[] }[] = [];

                    for (const result of stats) {
                        let name = result.period.year.toString();

                        if (result.period.month) {
                            name += '-' + result.period.month;
                        }

                        if (result.period.day) {
                            name += '-' + result.period.day;
                        }

                        const item: { name: string, series: any[] } = {
                            name,
                            series: [],
                        };

                        for (const key of keys) {
                            item.series.push({
                                name: key,
                                value: result[key],
                            })
                        }

                        results.push(item);
                    }

                    return results;
                })
            );

        this.salesStatsPerChannel$ = combineLatest([salesStats$, channels$])
            .pipe(
                map((data: [BsAdministrationOrderSalesStatsModel[], any]) => {
                    const stats = data[0];

                    const keys: string[] = this.channels.filter((channel) => data[1][channel]);
                    const results: { name: string, series: any[] }[] = [];

                    for (const key of keys) {
                        results.push({
                            name: key,
                            series: [],
                        });
                    }

                    for (const result of stats) {
                        let name = result.period.year.toString();

                        if (result.period.month) {
                            name += '-' + result.period.month;
                        }

                        if (result.period.day) {
                            name += '-' + result.period.day;
                        }

                        keys.forEach((value, index) => {
                            results[index].series.push({
                                name,
                                value: result[value],
                            });
                        });
                    }

                    return results;
                })
            );


        this.salesStatsTotal$ = combineLatest([salesStats$, channels$])
            .pipe(
                map((data: [BsAdministrationOrderSalesStatsModel[], any]) => {
                    const stats = data[0];

                    const keys: string[] = this.channels.filter((channel) => data[1][channel]);
                    let total = 0;

                    for (const result of stats) {
                        for (const key of keys) {
                            total += result[key];
                        }
                    }

                    return total;
                }),
            );
    }

    onChannelEnter(channel: string) {
        this.activeEntries$.next([{name: channel}]);
    }

    onChannelLeave() {
        this.activeEntries$.next([]);
    }
}
