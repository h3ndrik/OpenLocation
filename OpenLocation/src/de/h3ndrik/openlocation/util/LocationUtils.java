package de.h3ndrik.openlocation.util;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import de.h3ndrik.openlocation.DBAdapter;
import de.h3ndrik.openlocation.DBAdapter.Markings;

import android.location.Location;

public class LocationUtils {
    public static void removeJitter(JSONArray data, Markings markings, Location lastUploadedLocation) {
        
        /* prepare location array */
        Location[] location = new Location[data.length()+1];
        
        location[0] = lastUploadedLocation;
        for (Integer i = 1; i <= data.length(); i++) {
            try {
                JSONObject row = data.getJSONObject(i-1);
                location[i] = new Location("removeJitter");
                location[i].setTime(Long.valueOf(row.getString(DBAdapter.Contract.COLUMN_TIME)).longValue());
                location[i].setLatitude(Double.valueOf(row.getString(DBAdapter.Contract.COLUMN_LATITUDE)).doubleValue());
                location[i].setLongitude(Double.valueOf(row.getString(DBAdapter.Contract.COLUMN_LONGITUDE)).doubleValue());
                location[i].setAltitude(Double.valueOf(row.getString(DBAdapter.Contract.COLUMN_ALTITUDE)).doubleValue());
                location[i].setAccuracy(Float.valueOf(row.getString(DBAdapter.Contract.COLUMN_ACCURACY)).floatValue());
                location[i].setSpeed(Float.valueOf(row.getString(DBAdapter.Contract.COLUMN_SPEED)).floatValue());
                location[i].setBearing(Float.valueOf(row.getString(DBAdapter.Contract.COLUMN_BEARING)).floatValue());
                location[i].setProvider(row.getString(DBAdapter.Contract.COLUMN_PROVIDER));
            }
            catch (JSONException e) {
                // TODO Auto-generated catch block
                e.printStackTrace();
            }
            catch (NumberFormatException e) {
                e.printStackTrace();
            }
        }
        
        if (location.length >= 3)  {  // skip if we don't have enough data for this algorithm
	        for (Integer i = 1; i < location.length-1; i++) {  // without first and last element
	        	
	        	/* remove when we have better data in interval */
	            if (location[i].getTime()-location[i-1].getTime() < 15 * 60 * 1000 / 2
	             || location[i+1].getTime()-location[i].getTime() < 15 * 60 * 1000 / 2)
	            	if (location[i].getAccuracy() > location[i-1].getAccuracy()*3
	            	 || location[i].getAccuracy() > location[i+1].getAccuracy()*3)
	                {
	                    location[i].setProvider(location[i].getProvider() + "/jitter");
	                    try {
	        				JSONObject newloc = new JSONObject();
	        				newloc.put(DBAdapter.Contract.COLUMN_TIME, Long.toString(location[i].getTime()));
	        				newloc.put(DBAdapter.Contract.COLUMN_LATITUDE, Double.toString(location[i].getLatitude()));
	        				newloc.put(DBAdapter.Contract.COLUMN_LONGITUDE, Double.toString(location[i].getLongitude()));
	        				newloc.put(DBAdapter.Contract.COLUMN_ALTITUDE, Double.toString(location[i].getAltitude()));
	        				newloc.put(DBAdapter.Contract.COLUMN_ACCURACY, Float.toString(location[i].getAccuracy()));
	        				newloc.put(DBAdapter.Contract.COLUMN_SPEED, Float.toString(location[i].getSpeed()));
	        				newloc.put(DBAdapter.Contract.COLUMN_BEARING, Float.toString(location[i].getBearing()));
	        				newloc.put(DBAdapter.Contract.COLUMN_PROVIDER, location[i].getProvider());

							data.put(i-1, newloc);
						}
						catch (JSONException e) {
							// TODO Auto-generated catch block
							e.printStackTrace();
						}
	                    // location[i].reset();
	                    // location[i] = null;
	                    markings.setMarkingAt(i-1, DBAdapter.Contract.MARKED_JITTER);
	                }
	        	
	        	/* remove single spikes */
	            if (location[i-1].distanceTo(location[i+1]) < location[i-1].getAccuracy()
	             || location[i-1].distanceTo(location[i+1]) < location[i+1].getAccuracy())  // i-1 and i+1 are close
	                if (location[i].distanceTo(location[i-1]) > location[i-1].getAccuracy()
	                 && location[i].distanceTo(location[i+1]) > location[i+1].getAccuracy())  // but not i
	                {
	                    location[i].setProvider(location[i].getProvider() + "/jitter");
	                    try {
	        				JSONObject newloc = new JSONObject();
	        				newloc.put(DBAdapter.Contract.COLUMN_TIME, Long.toString(location[i].getTime()));
	        				newloc.put(DBAdapter.Contract.COLUMN_LATITUDE, Double.toString(location[i].getLatitude()));
	        				newloc.put(DBAdapter.Contract.COLUMN_LONGITUDE, Double.toString(location[i].getLongitude()));
	        				newloc.put(DBAdapter.Contract.COLUMN_ALTITUDE, Double.toString(location[i].getAltitude()));
	        				newloc.put(DBAdapter.Contract.COLUMN_ACCURACY, Float.toString(location[i].getAccuracy()));
	        				newloc.put(DBAdapter.Contract.COLUMN_SPEED, Float.toString(location[i].getSpeed()));
	        				newloc.put(DBAdapter.Contract.COLUMN_BEARING, Float.toString(location[i].getBearing()));
	        				newloc.put(DBAdapter.Contract.COLUMN_PROVIDER, location[i].getProvider());

							data.put(i-1, newloc);
						}
						catch (JSONException e) {
							// TODO Auto-generated catch block
							e.printStackTrace();
						}
	                    // location[i].reset();
	                    // location[i] = null;
	                    markings.setMarkingAt(i-1, DBAdapter.Contract.MARKED_JITTER);
	                }
	        }
        }
    }

}
