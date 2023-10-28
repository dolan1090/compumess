Shopware.Mixin.register('sw-publisher-activity', {
    methods: {
        getDateString(date) {
            const snippetPath = 'publisher.activity.dates';
            const editDate = new Date(date);

            const minutesPast = Math.round(Math.abs((Date.now() - editDate.getTime()) / (60 * 1000)));
            const minutesPerHour = 60;
            const minutesPerDay = 1440;
            const minutesPerWeek = 10080;

            if (minutesPast <= 5) {
                return this.$tc(`${snippetPath}.editedNow`);
            } else if (minutesPast <= minutesPerHour) {
                return this.$tc(`${snippetPath}.editedMinutesAgo`, minutesPast, { minutes: minutesPast });
            } else if (minutesPast <= minutesPerDay) {
                const hoursPast = Math.round(minutesPast / minutesPerHour);
                return this.$tc(`${snippetPath}.editedHoursAgo`, hoursPast, { hours: hoursPast });
            } else if (minutesPast <= minutesPerWeek) {
                const daysPast = Math.round(minutesPast / minutesPerDay);
                return this.$tc(`${snippetPath}.editedDaysAgo`, daysPast, { days: daysPast });
            } else {
                return Shopware.Utils.format.date(date, {
                    hour: 'numeric',
                    minute: 'numeric'
                });
            }
        }
    }
});
