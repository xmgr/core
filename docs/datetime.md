# DateTime

### __construct()

Create new object. The given datetime can be anything, a string, a timestamp, a static object or a DateTime object

```
$datetime->__construct([datetime: mixed = 'now'], [timezone: mixed = null], [immutable: bool = false])
```

### now()

Creates new object with current datetime (including microseconds)

```
$datetime->now(): DateTime
```


### isMutable()

Checks if the object is mutable.

```
$datetime->isMutable(): bool
```

### isImmutable()

Checks if the object is immutable.

```
$datetime->isImmutable(): bool
```

### setTimestamp()

Set timestamp.

```
$datetime->setTimestamp(timestamp: int): DateTime
```

### immutable()

Change object type to immutable.

```
$datetime->immutable(): DateTime
```

### mutable()

Change object type to mutable.

```
$datetime->mutable([mutable: bool = true]): DateTime
```

### toDateTime()

Returns a new `\DateTime` object with the object's date and time.

```
$datetime->toDateTime(): DateTime
```

### toDateTimeImmutable()

Returns a new `\DateTimeImmutable` object with the object's date and time.

```
$datetime->toDateTimeImmutable(): DateTimeImmutable
```

### diff()

Returns a `System\DateTimeDiff` object.

```
$datetime->diff([datetime: mixed = 'now']): DateTimeDiff
```

### earlierThan()

Checks if the current object is earlier than the given datetime.

```
$datetime->earlierThan(datetime: mixed): bool
```

### laterThan()

Checks if the current object is later than the given datetime.

```
$datetime->laterThan(datetime: mixed): bool
```

### format()

Formats the object's datetime to the given format.

```
$datetime->format(format: string): string
```

### toISO8601()

Returns date as ISO8601 formatted string.

```
$datetime->toISO8601([withT: bool = true], [withOffset: bool = false]): string
```

### toISO8601Expanded()

Returns date as expanded ISO8601 formatted string.

```
$datetime->toISO8601Expanded(): string
```

### toSqlFormat()

Returns datetime as "Y-m-d H:i:s"

```
$datetime->toSqlFormat(): string
```

### toString()

Returns datetime as "Y-m-d H:i:s"

```
$datetime->toString(): string
```

### toFullDateTimeString()

```
$datetime->toFullDateTimeString([delimiter: string = '']): string
```

### toW3CFormat()

```
$datetime->toW3CFormat(): string
```

### toTimeString()

Returns only the time (with or without seconds), separated by ":" or the given delimiter.

```
$datetime->toTimeString([withSeconds: bool = false], [delimiter: string = ":"]): string
```

### toSimpleFormat()

xxxxxxx

```
$datetime->toSimpleFormat(): string
```

### offsetInSeconds()

xxxxxxx

```
$datetime->offsetInSeconds(): int
```

### getUTCOffset()

xxxxxxx

```
$datetime->getUTCOffset([withColon: bool = true]): string
```

### timestamp()

xxxxxxx

```
$datetime->timestamp([withMicroseconds: bool = false]): float|int
```

### microseconds()

xxxxxxx

```
$datetime->microseconds(): float|int
```

### add()

xxxxxxx

```
$datetime->add([seconds: int|null = null], [minutes: int|null = null], [hours: int|null = null], [days: int|null = null], [months: int|null = null], [years: int|null = null]): DateTime
```

### sub()

xxxxxxx

```
$datetime->sub([seconds: int|null = null], [minutes: int|null = null], [hours: int|null = null], [days: int|null = null], [months: int|null = null], [years: int|null = null]): DateTime
```

### set()

xxxxxxx

```
$datetime->set([seconds: int|null = null], [minutes: int|null = null], [hours: int|null = null], [days: int|null = null], [months: int|null = null], [years: int|null = null]): DateTime
```

### random()

xxxxxxx

```
$datetime->random(): void
```

### applyFromNow()

xxxxxxx

```
$datetime->applyFromNow([seconds: bool = false], [minutes: bool = false], [hours: bool = false], [days: bool = false], [months: bool = false], [years: bool = false]): DateTime
```

### applyFromDateTime()

xxxxxxx

```
$datetime->applyFromDateTime(datetime: DateTime, [seconds: bool = false], [minutes: bool = false], [hours: bool = false], [days: bool = false], [months: bool = false], [years: bool = false]): DateTime
```

### addTime()

xxxxxxx

```
$datetime->addTime([hours: int|null = null], [minutes: int|null = null], [seconds: int|null = null]): DateTime
```

### subTime()

xxxxxxx

```
$datetime->subTime([hours: int|null = 0], [minutes: int|null = null], [seconds: int|null = null]): DateTime
```

### addDate()

xxxxxxx

```
$datetime->addDate([years: int|null = null], [months: int|null = null], [days: int|null = null]): DateTime
```

### subDate()

xxxxxxx

```
$datetime->subDate([years: int|null = null], [months: int|null = null], [days: int|null = null]): DateTime
```

### yesterday()

xxxxxxx

```
$datetime->yesterday(): DateTime
```

### setYear()

xxxxxxx

```
$datetime->setYear(year: int): DateTime
```

### addYears()

xxxxxxx

```
$datetime->addYears([years: int|null = null]): DateTime
```

### subYears()

xxxxxxx

```
$datetime->subYears([years: int|null = null]): DateTime
```

### setMonth()

xxxxxxx

```
$datetime->setMonth(month: int): DateTime
```

### addMonths()

xxxxxxx

```
$datetime->addMonths([months: int|null = null]): DateTime
```

### subMonths()

xxxxxxx

```
$datetime->subMonths([months: int|null = null]): DateTime
```

### setDay()

xxxxxxx

```
$datetime->setDay(day: int): DateTime
```

### addDays()

xxxxxxx

```
$datetime->addDays([days: int|null = null]): DateTime
```

### subDays()

xxxxxxx

```
$datetime->subDays([days: int|null = null]): DateTime
```

### setHour()

xxxxxxx

```
$datetime->setHour(hour: int): DateTime
```

### addHours()

xxxxxxx

```
$datetime->addHours([hours: int|null = null]): DateTime
```

### subHours()

xxxxxxx

```
$datetime->subHours([hours: int|null = null]): DateTime
```

### setMinute()

xxxxxxx

```
$datetime->setMinute(minute: int): DateTime
```

### addMinutes()

xxxxxxx

```
$datetime->addMinutes([minutes: int|null = null]): DateTime
```

### subMinutes()

xxxxxxx

```
$datetime->subMinutes([minutes: int|null = null]): DateTime
```

### setSecond()

xxxxxxx

```
$datetime->setSecond(second: int): DateTime
```

### addSeconds()

xxxxxxx

```
$datetime->addSeconds([seconds: int|null = null]): DateTime
```

### subSeconds()

xxxxxxx

```
$datetime->subSeconds([seconds: int|null = null]): DateTime
```

### setDayOfWeek()

xxxxxxx

```
$datetime->setDayOfWeek(dayOfWeek: int|null): DateTime
```

### nextWeek()

xxxxxxx

```
$datetime->nextWeek([dayOfWeek: int|null = null]): DateTime
```

### previousWeek()

xxxxxxx

```
$datetime->previousWeek([dayOfWeek: int|null = null]): DateTime
```

### dayOfWeek()

xxxxxxx

```
$datetime->dayOfWeek(): int
```

### dayOfWeekIs()

xxxxxxx

```
$datetime->dayOfWeekIs(dayOfWeek: array|int): bool
```

### isWeekend()

xxxxxxx

```
$datetime->isWeekend(): bool
```

### isWeekday()

xxxxxxx

```
$datetime->isWeekday(): bool
```

### isMonday()

xxxxxxx

```
$datetime->isMonday(): bool
```

### isTuesday()

xxxxxxx

```
$datetime->isTuesday(): bool
```

### isWednesday()

xxxxxxx

```
$datetime->isWednesday(): bool
```

### isThursday()

xxxxxxx

```
$datetime->isThursday(): bool
```

### isFriday()

xxxxxxx

```
$datetime->isFriday(): bool
```

### isSaturday()

xxxxxxx

```
$datetime->isSaturday(): bool
```

### isSunday()

xxxxxxx

```
$datetime->isSunday(): bool
```

### lastMonday()

xxxxxxx

```
$datetime->lastMonday(): DateTime
```

### thisMonday()

xxxxxxx

```
$datetime->thisMonday(): DateTime
```

### nextMonday()

xxxxxxx

```
$datetime->nextMonday(): DateTime
```

### lastTuesday()

xxxxxxx

```
$datetime->lastTuesday(): DateTime
```

### thisTuesday()

xxxxxxx

```
$datetime->thisTuesday(): DateTime
```

### nextTuesday()

xxxxxxx

```
$datetime->nextTuesday(): DateTime
```

### lastWednesday()

xxxxxxx

```
$datetime->lastWednesday(): DateTime
```

### thisWednesday()

xxxxxxx

```
$datetime->thisWednesday(): DateTime
```

### nextWednesday()

xxxxxxx

```
$datetime->nextWednesday(): DateTime
```

### lastThursday()

xxxxxxx

```
$datetime->lastThursday(): DateTime
```

### thisThursday()

xxxxxxx

```
$datetime->thisThursday(): DateTime
```

### nextThursday()

xxxxxxx

```
$datetime->nextThursday(): DateTime
```

### lastFriday()

xxxxxxx

```
$datetime->lastFriday(): DateTime
```

### thisFriday()

xxxxxxx

```
$datetime->thisFriday(): DateTime
```

### nextFriday()

xxxxxxx

```
$datetime->nextFriday(): DateTime
```

### lastSaturday()

xxxxxxx

```
$datetime->lastSaturday(): DateTime
```

### thisSaturday()

xxxxxxx

```
$datetime->thisSaturday(): DateTime
```

### nextSaturday()

xxxxxxx

```
$datetime->nextSaturday(): DateTime
```

### lastSunday()

xxxxxxx

```
$datetime->lastSunday(): DateTime
```

### thisSunday()

xxxxxxx

```
$datetime->thisSunday(): DateTime
```

### nextSunday()

xxxxxxx

```
$datetime->nextSunday(): DateTime
```

### setToLastDayOfMonth()

xxxxxxx

```
$datetime->setToLastDayOfMonth(): DateTime
```

### setFromFormat()

xxxxxxx

```
$datetime->setFromFormat(format: string, datetime: string): DateTime
```

### createFromFormat()

xxxxxxx

```
$datetime->createFromFormat(format: string, datetime: string): DateTime
```

### create()

xxxxxxx

```
$datetime->create(datetime: mixed, [timezone: mixed = null]): DateTime
```

### createImmutable()

xxxxxxx

```
$datetime->createImmutable(datetime: mixed, timezone: mixed): DateTime
```

### factory()

xxxxxxx

```
$datetime->factory(datetimes: array, timezone: mixed, [immutable: bool = false]): DateTime[]
```

### setTimezone()

xxxxxxx

```
$datetime->setTimezone([timezone: DateTimeZone|mixed|null|string = null]): DateTimeZone|string
```

### timezone()

xxxxxxx

```
$datetime->timezone(): DateTimeZone|false
```

### modify()

xxxxxxx

```
$datetime->modify(modifier: string): DateTime
```

### setDate()

xxxxxxx

```
$datetime->setDate([year: bool|float|int|mixed|null|string = null], [month: bool|float|int|mixed|null|string = null], [day: bool|float|int|mixed|null|string = null]): DateTime
```

### getDateString()

xxxxxxx

```
$datetime->getDateString([delimiter: string = '-']): string
```

### setTime()

xxxxxxx

```
$datetime->setTime([hour: mixed = null], [minute: mixed = null], [second: mixed = null]): DateTime
```

### daysInMonth()

xxxxxxx

```
$datetime->daysInMonth(): int
```

### isLastDayOfMonth()

xxxxxxx

```
$datetime->isLastDayOfMonth(): bool
```

### modifyYear()

xxxxxxx

```
$datetime->modifyYear([mod: int|null = null]): DateTime
```

### getYear()

xxxxxxx

```
$datetime->getYear(): int
```

### modifyMonth()

xxxxxxx

```
$datetime->modifyMonth([mod: int|null = null]): DateTime
```

### getMonth()

xxxxxxx

```
$datetime->getMonth(): int
```

### modifyDay()

xxxxxxx

```
$datetime->modifyDay([mod: int|null = null]): DateTime
```

### getDay()

xxxxxxx

```
$datetime->getDay(): int
```

### modifyHour()

xxxxxxx

```
$datetime->modifyHour([mod: int|null = null]): DateTime
```

### yearIs()

xxxxxxx

```
$datetime->yearIs(year: array|int): bool
```

### monthIs()

xxxxxxx

```
$datetime->monthIs(month: array|int): bool
```

### dayIs()

xxxxxxx

```
$datetime->dayIs(day: array|int): bool
```

### hoursIs()

xxxxxxx

```
$datetime->hoursIs(hour: array|int): bool
```

### minuteIs()

xxxxxxx

```
$datetime->minuteIs(minute: array|int): bool
```

### secondIs()

xxxxxxx

```
$datetime->secondIs(second: array|int): bool
```

### getHour()

xxxxxxx

```
$datetime->getHour(): int
```

### modifyMinute()

xxxxxxx

```
$datetime->modifyMinute([mod: int|null = null]): DateTime
```

### getMinute()

xxxxxxx

```
$datetime->getMinute(): int
```

### modifySecond()

xxxxxxx

```
$datetime->modifySecond([mod: int|null = null]): DateTime
```

### getSecond()

xxxxxxx

```
$datetime->getSecond(): int
```

### mod()

xxxxxxx

```
$datetime->mod([years: int|null = null], [months: int|null = null], [weeks: int|null = null], [days: int|null = null], [hours: int|null = null], [minutes: int|null = null], [seconds: int|null = null]): DateTime
```

### setToNow()

xxxxxxx

```
$datetime->setToNow(): DateTime
```

### resetTime()

xxxxxxx

```
$datetime->resetTime(): DateTime
```

### timeToMidnight()

xxxxxxx

```
$datetime->timeToMidnight(): DateTime
```

### startTime()

xxxxxxx

```
$datetime->startTime(): DateTime
```

### endTime()

xxxxxxx

```
$datetime->endTime(): DateTime
```

### timeToEndOfTheDay()

xxxxxxx

```
$datetime->timeToEndOfTheDay(): DateTime
```

### toNextDay()

xxxxxxx

```
$datetime->toNextDay(): DateTime
```

### toStartOfThisWeek()

xxxxxxx

```
$datetime->toStartOfThisWeek(): DateTime
```

### toStartOfNextWeek()

xxxxxxx

```
$datetime->toStartOfNextWeek(): DateTime
```

### toStartOfNextMonth()

xxxxxxx

```
$datetime->toStartOfNextMonth(): DateTime
```

### toStartOfNextYear()

xxxxxxx

```
$datetime->toStartOfNextYear(): DateTime
```

### isToday()

xxxxxxx

```
$datetime->isToday(): bool
```

### isTomorrow()

xxxxxxx

```
$datetime->isTomorrow(): bool
```

### isYesterday()

xxxxxxx

```
$datetime->isYesterday(): bool
```

### isNow()

xxxxxxx

```
$datetime->isNow(): bool
```

### copy()

xxxxxxx

```
$datetime->copy(): DateTime
```

### copies()

xxxxxxx

```
$datetime->copies([amount: int = 1]): array
```

### timeIs()

xxxxxxx

```
$datetime->timeIs([hour: int|null = null], [minute: int|null = null], [second: int|null = null]): bool
```

### dateIs()

xxxxxxx

```
$datetime->dateIs([year: int|null = null], [month: int|null = null], [day: int|null = null]): bool
```

### isPast()

xxxxxxx

```
$datetime->isPast(): bool
```

### isFuture()

xxxxxxx

```
$datetime->isFuture(): bool
```

### expired()

xxxxxxx

```
$datetime->expired(): bool
```

### save()

xxxxxxx

```
$datetime->save(name: string): DateTime
```

### saved()

xxxxxxx

```
$datetime->saved(key): \DateTime|DateTimeImmutable|mixed|DateTime
```

### restore()

xxxxxxx

```
$datetime->restore(name: string): DateTime
```
