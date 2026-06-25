<script setup lang="ts">
import type { CalendarRootEmits, CalendarRootProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarHeader,
    CalendarHeading,
    CalendarNext,
    CalendarPrev,
    CalendarRoot,
    useForwardPropsEmits,
} from 'reka-ui';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { reactiveOmit } from '@vueuse/core';
import { cn } from '@/lib/utils';

const props = defineProps<CalendarRootProps & { class?: HTMLAttributes['class'] }>();
const emits = defineEmits<CalendarRootEmits>();

const delegatedProps = reactiveOmit(props, 'class');
const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <CalendarRoot
        v-slot="{ grid, weekDays }"
        data-slot="calendar"
        v-bind="forwarded"
        :class="cn('rounded-md border border-white/10 bg-[#181818] p-3 text-white', props.class)"
    >
        <CalendarHeader class="relative flex items-center justify-center pb-2">
            <CalendarPrev
                class="absolute left-1 inline-flex h-8 w-8 items-center justify-center border border-white/10 bg-[#202020] text-zinc-300 transition hover:border-[#f4a340] hover:text-[#f4a340] disabled:pointer-events-none disabled:opacity-50"
            >
                <ChevronLeft class="h-4 w-4" />
            </CalendarPrev>
            <CalendarHeading class="text-sm font-bold" />
            <CalendarNext
                class="absolute right-1 inline-flex h-8 w-8 items-center justify-center border border-white/10 bg-[#202020] text-zinc-300 transition hover:border-[#f4a340] hover:text-[#f4a340] disabled:pointer-events-none disabled:opacity-50"
            >
                <ChevronRight class="h-4 w-4" />
            </CalendarNext>
        </CalendarHeader>

        <div class="flex flex-col gap-y-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
            <CalendarGrid
                v-for="month in grid"
                :key="month.value.toString()"
                class="w-full border-collapse space-y-1"
            >
                <CalendarGridHead>
                    <CalendarGridRow class="mb-1 flex">
                        <CalendarHeadCell
                            v-for="day in weekDays"
                            :key="day"
                            class="w-9 rounded-md text-[0.8rem] font-normal text-zinc-500"
                        >
                            {{ day }}
                        </CalendarHeadCell>
                    </CalendarGridRow>
                </CalendarGridHead>
                <CalendarGridBody>
                    <CalendarGridRow
                        v-for="(weekDates, index) in month.rows"
                        :key="`week-${index}`"
                        class="mt-2 flex w-full"
                    >
                        <CalendarCell
                            v-for="weekDate in weekDates"
                            :key="weekDate.toString()"
                            :date="weekDate"
                            class="relative h-9 w-9 p-0 text-center text-sm"
                        >
                            <CalendarCellTrigger
                                :day="weekDate"
                                :month="month.value"
                                class="inline-flex h-9 w-9 items-center justify-center border border-transparent text-sm transition hover:border-[#f4a340] hover:bg-[#241b12] data-[disabled]:pointer-events-none data-[outside-view]:text-zinc-700 data-[selected]:border-[#f4a340] data-[selected]:bg-[#f4a340] data-[selected]:font-black data-[selected]:text-black data-[today]:border-white/30 data-[unavailable]:pointer-events-none data-[unavailable]:text-zinc-700"
                            />
                        </CalendarCell>
                    </CalendarGridRow>
                </CalendarGridBody>
            </CalendarGrid>
        </div>
    </CalendarRoot>
</template>
