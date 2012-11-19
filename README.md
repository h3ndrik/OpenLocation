OpenLocation
============

Track your position / Where are your friends?


Description
--------------------------------------
Track your position / Where are your friends?
OpenLocation peroidically sends the location of your android-phone to a webserver. Both client and server are open source and you can host the database which contains all your previous locations on your own server.
Sample server on: http://location.h3ndrik.de


How it works
--------------------------------------
OpenLocation will keep track of the following information:
- your location
- your altitude, speed and bearing
- how the data was obtained (GPS, triangulating over cell-towers/wifi, ...)
- a timestamp and the current ip-address of the client

The android-client will cache _every_ position information obtained by the system and periodically send them to the server.
This should be implemented in a battery-conserving way. E.g. OpenLocation uses the location data of the network or data that was requested by other apps anyhow. Only if there is no data for 15min the app will get active. Data will be transferred if the internet connection is active anyhow, but at least once every 20min (and at most every 5min).


Requirements
--------------------------------------
Client:
- Phone with at least Android version 2.2 (API level 8)
Server:
- Webserver with PHP and MySQL
- ability to open outgoing http connections (used to query other user's location on different servers)

Install
--------------------------------------
Client:
- Install apk on any recent Android smartphone
- Enable some kind of data connection and "Background data"
Server:
- Copy files to Webspace
- Copy config.php.sample to config.php and edit

Attribution
--------------------------------------
Rendering of the map is done server-side by Leaflet (leafletjs.com), Map data (CC-BY-SA) OpenStreetMap contributors (openstreetmap.org), Imagery (c) CloudMade (cloudmade.com).

Copyright
--------------------------------------
Copyright (c) 2012 Hendrik Langer <dev@h3ndrik.de>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
