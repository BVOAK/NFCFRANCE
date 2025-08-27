/**
 * Start time zone widget script
 */

(function ($, elementor) {
    'use strict';
    var widgetTimeZone = function ($scope, $) {
        var $TimeZone = $scope.find('.bdt-time-zone'),
            $settings = $TimeZone.data('settings'),
            timeFormat,
            offset = $settings.gmt,
            dateFormat = $settings.dateFormat,
            enableDST = $settings.enableDST === 'yes';

        if (!$TimeZone.length) {
            return;
        }

        var timeZoneApp = {
            digitalClock: function () {
                if ($settings.timeHour == '12h') {
                    timeFormat = '%I:%M:%S %p';
                } else {
                    timeFormat = '%H:%M:%S';
                }
                var dateFormat = $settings.dateFormat;
                if (dateFormat != 'emptyDate') {
                    dateFormat = '<div class=\"bdt-time-zone-date\"> ' + $settings.dateFormat + ' </div>'
                } else {
                    dateFormat = '';
                }
                var country;
                if ($settings.country != 'emptyCountry') {
                    country = '<div  class=\"bdt-time-zone-country\">' + $settings.country + '</div>';
                } else {
                    country = ' ';
                }
                
                // Check if we should apply DST
                const currentDate = new Date();
                let finalOffset = offset;
                let dstIndicator = '';
                
                if (enableDST && this.isDSTActive(currentDate, offset)) {
                    // Add 1 hour for DST if not using local time
                    if (offset !== 'local') {
                        finalOffset = parseFloat(offset) + 1;
                    }
                    // Add DST indicator with consistent styling
                    dstIndicator = '<small class="bdt-dst-indicator" style="margin-left: 5px;">DST</small>';
                }
                
                var timeZoneFormat = '<div class=\"bdt-time-zone-dt\"> ' + country + ' ' + dateFormat + 
                                    ' <div class=\"bdt-time-zone-time\">' + timeFormat + dstIndicator + '</div> </div>';

                if (offset == '') return;
                
                var options = {
                    format: timeZoneFormat,
                    timeNotation: $settings.timeHour,
                    am_pm: true,
                    utc: (offset == 'local') ? false : true,
                    utcOffset: (offset == 'local') ? null : finalOffset,
                }

                $('#' + $settings.id).jclock(options);
            },
            isDSTActive: function(date, offset) {
                // If DST is disabled in settings, return false
                if (!enableDST) return false;
                
                // If using local time, check browser's DST detection
                if (offset === 'local') {
                    // Compare January and July to see if DST is observed
                    const jan = new Date(date.getFullYear(), 0, 1).getTimezoneOffset();
                    const jul = new Date(date.getFullYear(), 6, 1).getTimezoneOffset();
                    const isDstObserved = jan !== jul;
                    
                    if (!isDstObserved) return false;
                    
                    // If DST is observed, check if it's currently active
                    const currentOffset = date.getTimezoneOffset();
                    return currentOffset === Math.min(jan, jul);
                }
                
                // For specific timezones, use a more accurate approach
                // Numeric offset is assumed to be GMT+X or GMT-X
                
                // Get the current month and day
                const month = date.getMonth(); // 0-11
                const day = date.getDate();    // 1-31
                const numericOffset = parseFloat(offset);
                
                // General DST rules for major regions:
                
                // Northern Hemisphere (Europe, North America, Asia)
                // DST typically starts on last Sunday in March and ends on last Sunday in October
                if (numericOffset >= -12 && numericOffset <= 14) {
                    // Northern hemisphere (rough approximation)
                    if (numericOffset > 0) {
                        // March (2) after ~last Sunday to October (9) before ~last Sunday
                        if (month > 2 && month < 9) return true;
                        
                        // Edge cases: last week of March and last week of October
                        if (month === 2 && day >= 25) return true; // Approx last week of March
                        if (month === 9 && day <= 25) return true; // Approx last week of October
                    }
                    // Southern hemisphere (Australia, South America, South Africa, etc.)
                    else if (numericOffset < 0 && numericOffset >= -12) {
                        // September (8) after ~first Sunday to April (3) before ~first Sunday
                        if (month < 3 || month > 8) return true;
                        
                        // Edge cases: first week of April and last week of September
                        if (month === 3 && day <= 7) return true; // Approx first week of April
                        if (month === 8 && day >= 25) return true; // Approx last week of September
                    }
                }
                
                return false;
            },
            convertToTimeZoneAndFormat: function (date, offset) {
                // Get the UTC time in milliseconds
                const utcTime = date.getTime() + (date.getTimezoneOffset() * 60000);

                // Apply DST correction if enabled and active
                let dstOffset = 0;
                if (enableDST && this.isDSTActive(date, offset)) {
                    dstOffset = 1; // Add one hour for DST
                }

                // Calculate the target time using the offset and DST if applicable
                const targetTime = new Date(utcTime + ((parseFloat(offset) + dstOffset) * 3600000));

                // Extract hours, minutes, and seconds
                let hours = targetTime.getHours(),
                    minutes = targetTime.getMinutes(),
                    seconds = targetTime.getSeconds();
                const ampm = hours >= 12 ? 'PM' : 'AM',
                    getDate = targetTime.toDateString();
                hours = hours % 12 || 12; // Convert to 12-hour format and handle midnight (0 AM)

                // Add leading zeros to single-digit minutes and seconds
                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;

                return {
                    hours,
                    minutes,
                    seconds,
                    ampm,
                    getDate,
                };
            },
            formatDate: function (inputDate, formatOption) {
                var date = new Date(inputDate),
                    selectedFormat = formatOption;

                if (!selectedFormat) {
                    console.error('Invalid format option');
                    return '';
                }

                // Replace format placeholders
                var formattedDate = selectedFormat.replace(/%([a-zA-Z])/g, function (_, formatCode) {
                    switch (formatCode) {
                        case 'd':
                            return String(date.getDate()).padStart(2, '0');
                        case 'm':
                            return String(date.getMonth() + 1).padStart(2, '0');
                        case 'y':
                            return String(date.getFullYear()).slice(-2);
                        case 'Y':
                            return String(date.getFullYear());
                        case 'b':
                            return date.toLocaleString('default', {
                                month: 'short'
                            });
                        case 'a':
                            return date.toLocaleString('default', {
                                weekday: 'short'
                            });
                        default:
                            return formatCode;
                    }
                });

                return formattedDate;
            },
            date: function () {
                let localDate = new Date(),
                    targetOffset = offset,
                    result = timeZoneApp.convertToTimeZoneAndFormat(localDate, targetOffset),
                    date = result.getDate;

                const formattedDate = this.formatDate(date, dateFormat);
                $($TimeZone).find('.bdt-time-zone-date').text(formattedDate);
            },
            updateTime: function () {
                const self = this;
                
                setInterval(function () {
                    let localDate = new Date(),
                        targetOffset = ('local' === offset) ? localDate.getTimezoneOffset() / -60 : offset,
                        result = timeZoneApp.convertToTimeZoneAndFormat(localDate, targetOffset);

                    let second = result.seconds * 6,
                        minute = result.minutes * 6 + second / 60,
                        hour = ((result.hours % 12) / 12) * 360 + 90 + minute / 12;

                    $($TimeZone).find('.bdt-clock-hour').css("transform", "rotate(" + hour + "deg)");
                    $($TimeZone).find('.bdt-clock-minute').css("transform", "rotate(" + minute + "deg)");
                    $($TimeZone).find('.bdt-clock-second').css("transform", "rotate(" + second + "deg)");
                    $($TimeZone).find('.bdt-clock-am-pm').text(result.ampm);
                    
                    // Add or remove DST indicator for analog clock
                    const isDstActive = self.isDSTActive(localDate, targetOffset);
                    const $dstIndicator = $($TimeZone).find('.bdt-dst-indicator');
                    
                    if (isDstActive && enableDST) {
                        if ($dstIndicator.length === 0) {
                            const $indicator = $('<small class="bdt-dst-indicator" style="margin-left: 5px;">DST</small>');
                            $($TimeZone).find('.bdt-clock-am-pm').append($indicator);
                        }
                    } else {
                        $dstIndicator.remove();
                    }

                }, 1000);

                this.date();
            },
            init: function () {
                if ('digital' == $settings.clock_style) {
                    this.digitalClock();
                } else {
                    this.updateTime();
                }
            }
        }

        epObserveTarget($scope[0], function () {
            timeZoneApp.init();
        });
    };
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/bdt-time-zone.default', widgetTimeZone);
    });
}(jQuery, window.elementorFrontend));

/**
 * End time zone widget script
 */
