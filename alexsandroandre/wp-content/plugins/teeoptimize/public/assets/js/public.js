(function(a){a.fn.dsCountDown=function(b){var c=this;c.data={refreshed:1000,thread:null,running:false,left:0,decreament:1,interval:0,seconds:0,minutes:0,hours:0,days:0,elemDays:null,elemHours:null,elemMinutes:null,elemSeconds:null};var d={startDate:new Date(),endDate:null,elemSelDays:"",elemSelHours:"",elemSelMinutes:"",elemSelSeconds:"",theme:"white",titleDays:"Days",titleHours:"Hrs",titleMinutes:"Min",titleSeconds:"Sec",onBevoreStart:null,onClocking:null,onFinish:null};c.options=a.extend({},d,b);if(this.length>1){this.each(function(){a(this).dsCountDown(b)});return this}c.init=function(){if(!c.options.elemSelSeconds){c.prepend('<div class="ds-element ds-element-seconds"><div class="ds-element-value ds-seconds">00</div><div class="ds-element-title">'+c.options.titleSeconds+"</div></div>");c.data.elemSeconds=c.find(".ds-seconds")}else{c.data.elemSeconds=c.find(c.options.elemSelSeconds)}if(!c.options.elemSelMinutes){c.prepend('<div class="ds-element ds-element-minutes"><div class="ds-element-value ds-minutes">00</div><div class="ds-element-title">'+c.options.titleMinutes+"</div></div>");c.data.elemMinutes=c.find(".ds-minutes")}else{c.data.elemMinutes=c.find(c.options.elemSelMinutes)}if(!c.options.elemSelHours){c.prepend('<div class="ds-element ds-element-hours"><div class="ds-element-value ds-hours">00</div><div class="ds-element-title">'+c.options.titleHours+"</div></div>");c.data.elemHours=c.find(".ds-hours")}else{c.data.elemHours=c.find(c.options.elemSelHours)}if(!c.options.elemSelDays){c.prepend('<div class="ds-element ds-element-days"><div class="ds-element-value ds-days">00</div><div class="ds-element-title">'+c.options.titleDays+"</div></div>");c.data.elemDays=c.find(".ds-days")}else{c.data.elemDays=c.find(c.options.elemSelDays)}c.addClass("dsCountDown");c.addClass("ds-"+c.options.theme);if(c.options.startDate&&c.options.endDate){c.data.interval=c.options.endDate.getTime()-c.options.startDate.getTime();if(c.data.interval>0){var g=(c.data.interval/1000);var f=(g%86400);var e=(f%3600);c.data.left=g;c.data.days=Math.floor(g/86400);c.data.hours=Math.floor(f/3600);c.data.minutes=Math.floor(e/60);c.data.seconds=Math.floor(e%60)}}c.start()};c.stop=function(e){if(c.data.running){clearInterval(c.data.thread);c.data.running=false}if(e){e(c)}};c.start=function(){a("#logger").append("<br/>Start");if(!c.data.running){a("#logger").append("<br/>Clock");if(c.data.left>0){if(c.options.onBevoreStart){c.options.onBevoreStart(c)}c.data.thread=setInterval(function(){if(c.data.left>0){c.data.left-=c.data.decreament;c.data.seconds-=c.data.decreament;if(c.data.seconds<=0&&(c.data.minutes>0||c.data.hours>0||c.data.days>0)){c.data.minutes--;c.data.seconds=60}if(c.data.minutes<=0&&(c.data.hours>0||c.data.days>0)){c.data.hours--;c.data.minutes=60}if(c.data.hours<=0&&c.data.days>0){c.data.days--;c.data.hours=24}if(c.data.elemDays){c.data.elemDays.html((c.data.days<10?"0"+c.data.days:c.data.days))}if(c.data.elemHours){c.data.elemHours.html((c.data.hours<10?"0"+c.data.hours:c.data.hours))}if(c.data.elemMinutes){c.data.elemMinutes.html((c.data.minutes<10?"0"+c.data.minutes:c.data.minutes))}if(c.data.elemSeconds){c.data.elemSeconds.html((c.data.seconds<10?"0"+c.data.seconds:c.data.seconds))}if(c.options.onClocking){c.options.onClocking(c)}}else{c.stop(c.options.onFinish)}},c.data.refreshed);c.data.running=true}else{if(c.options.onFinish){c.options.onFinish(c)}}}};c.init()}})(jQuery);