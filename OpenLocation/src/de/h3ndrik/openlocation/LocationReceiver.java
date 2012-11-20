package de.h3ndrik.openlocation;

import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.util.ArrayList;
import java.util.List;
import java.util.zip.Deflater;
import java.util.zip.Inflater;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.auth.AuthScope;
import org.apache.http.auth.UsernamePasswordCredentials;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.params.BasicHttpParams;
import org.apache.http.params.HttpParams;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import de.h3ndrik.openlocation.util.Utils;

import android.app.AlarmManager;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager.NameNotFoundException;
import android.database.Cursor;
import android.location.Location;
import android.location.LocationManager;
import android.location.LocationListener;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.util.Base64;
import android.util.Log;
import android.widget.Toast;

public class LocationReceiver extends BroadcastReceiver {
	private static final String DEBUG_TAG = "LocationReceiver"; // for logging
																// purposes

	static LocationListener locationListener;

	@Override
	public void onReceive(Context context, Intent intent) {

		Log.d(DEBUG_TAG, "got a Broadcast");

		if (intent.hasExtra(LocationManager.KEY_LOCATION_CHANGED)) {
			Location location = (Location) intent.getExtras().get(
					LocationManager.KEY_LOCATION_CHANGED);
			Log.d(DEBUG_TAG, "location update by " + location.getProvider());
			// Toast.makeText(context, "Location changed : Lat: " +
			// location.getLatitude() + " Long: " + location.getLongitude(),
			// Toast.LENGTH_SHORT).show();
			// in sqlite schreiben
			DBAdapter db = new DBAdapter(context);
			db.dbhelper.open_w();
			db.dbhelper.insertLocation(location.getTime(),
					location.getLatitude(), location.getLongitude(),
					location.getAltitude(), location.getAccuracy(),
					location.getSpeed(), location.getBearing(),
					location.getProvider());
			db.dbhelper.close();
		} else if (intent.hasExtra("de.h3ndrik.openlocation.cancelgps")) {
			Log.d(DEBUG_TAG, "Alarm: cancel GPS");

			if (locationListener != null) {
				// Toast.makeText(context, "canceling GPS",
				// Toast.LENGTH_SHORT).show();
				LocationManager locationManager = (LocationManager) context
						.getSystemService(Context.LOCATION_SERVICE);
				// Toast.makeText(context, "removing LocationListener",
				// Toast.LENGTH_SHORT).show();
				locationManager.removeUpdates(locationListener);
				//give LocationManager time to settle
				try {
					Thread.sleep(100);
				} catch (InterruptedException e) {
					// continue
				}
				locationManager.requestLocationUpdates(
						LocationManager.NETWORK_PROVIDER, 0, 0,
						locationListener);	// Request network location instead
			}
		}
	}



	public LocationReceiver() {
	}
}
