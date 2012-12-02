package de.h3ndrik.openlocation.util;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import de.h3ndrik.openlocation.DBAdapter;
import de.h3ndrik.openlocation.DBAdapter.Markings;

import android.location.Location;

public class LocationUtils {
    public static void removeJitter(JSONArray data, Markings markings) {
        if (data.length() < 3)  return;  // skip if we don't have enough data for this algorithm
        
        /* prepare location array */
        Location[] location = new Location[data.length()];
        for (Integer i = 0; i < data.length(); i++) {
            try {
                JSONObject row = data.getJSONObject(i);
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
        
        
        for (Integer i = 1; i < data.length()-1; i++) {  // without first last element
        	/* remove jitter */
            if (location[i-1].distanceTo(location[i+1]) < location[i-1].getAccuracy()
             || location[i-1].distanceTo(location[i+1]) < location[i+1].getAccuracy())  // i-1 and i+1 are close
                if (location[i].distanceTo(location[i-1]) > location[i-1].getAccuracy()
                 && location[i].distanceTo(location[i+1]) > location[i+1].getAccuracy())  // but not i
                {
                    location[i].setProvider(location[i].getProvider() + "/jitter");
                    try {
						data.put(i, null);
					}
					catch (JSONException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
                    // location[i].reset();
                    // location[i] = null;
                    markings.setMarkingAt(i, DBAdapter.Contract.MARKED_JITTER);
                }
        }
    }

}
