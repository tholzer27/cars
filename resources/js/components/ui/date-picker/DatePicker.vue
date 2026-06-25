<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import type { DateValue } from '@internationalized/date';
import {
    DateFormatter,
    getLocalTimeZone,
    parseDate,
    today,
} from '@internationalized/date';
import { CalendarDays } from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
        class?: string;
    }>(),
    {
        modelValue: '',
        placeholder: 'Datum auswählen',
        class: '',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const formatter = new DateFormatter('de-CH', { dateStyle: 'medium' });
const value = computed<DateValue | undefined>(() => {
    if (!props.modelValue) return undefined;

    try {
        return parseDate(props.modelValue);
    } catch {
        return undefined;
    }
});
const label = computed(() =>
    value.value
        ? formatter.format(value.value.toDate(getLocalTimeZone()))
        : props.placeholder,
);
</script>

<template>
    <Popover v-slot="{ close }">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                :class="
                    cn(
                        'h-9 w-full justify-start rounded-none border-white/10 bg-[#181818] px-3 text-left text-sm font-semibold text-white hover:border-[#f4a340] hover:bg-[#181818] hover:text-white',
                        !value && 'text-zinc-500 hover:text-zinc-500',
                        props.class,
                    )
                "
            >
                <CalendarDays class="h-4 w-4 text-[#f4a340]" />
                {{ label }}
            </Button>
        </PopoverTrigger>
        <PopoverContent
            class="w-auto border-white/10 bg-[#181818] p-0"
            align="start"
        >
            <Calendar
                :model-value="value"
                :default-placeholder="value ?? today(getLocalTimeZone())"
                layout="month-and-year"
                initial-focus
                @update:model-value="
                    (nextValue) => {
                        if (!nextValue) return;
                        emit('update:modelValue', nextValue.toString());
                        close();
                    }
                "
            />
        </PopoverContent>
    </Popover>
</template>
