var currentTime = new Date()
var hours = currentTime.getHours()
var minutes = currentTime.getMinutes()

var suffix = "AM";
if (hours >= 12) {
suffix = "PM";
hours = hours - 12;
  }


if (minutes < 10)
minutes = "0" + minutes

if (suffix == "PM" && hours >= 4)
    {
 document.write("");
}
    else
    {
    var hoursLeft = 5 - hours;
var minsLeft = 60 - minutes;
    document.write("<h3> Få leveret i dag, hvis du bestiller inden<br>" + hoursLeft  + " time(r) og " + minsLeft + " minutter</h3>")
    }