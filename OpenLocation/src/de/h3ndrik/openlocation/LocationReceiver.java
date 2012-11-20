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
	private static final String DEBUG_TAG = "LocationReceiver"; // for logging purposes

	private static LocationListener locationListener = null;
	
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
			
			// write to SQLite
			DBAdapter db = new DBAdapter(context);
			db.dbhelper.open_w();
			db.dbhelper.insertLocation(location.getTime(),
					location.getLatitude(), location.getLongitude(),
					location.getAltitude(), location.getAccuracy(),
					location.getSpeed(), location.getBearing(),
					location.getProvider());
			db.dbhelper.close();
		} 

		if (intent.hasExtra("de.h3ndrik.openlocation.cancelgps")) {
			Log.d(DEBUG_TAG, "cancel GPS");

			cancelUpdates(context);
		}
	}
	
	public static void doActiveUpdate(final Context context) {
		Log.d(DEBUG_TAG, "force active location update");
		
		if (locationListener == null) {
			Log.d(DEBUG_TAG, "set up new LocationListener");
			
			locationListener = new LocationListener() {
				public void onLocationChanged(Location location) {
					Log.d(DEBUG_TAG, "locationListener: location changed. disabling");
					LocationManager locationManager = (LocationManager) context
							.getSystemService(Context.LOCATION_SERVICE);
					// give location provider time to settle
					try {
						Thread.sleep(4000);
					} catch (InterruptedException e) {
						// continue
					}
					locationManager.removeUpdates(this);
					locationListener = null;
				}

				public void onProviderDisabled(String provider) {
					// TODO Auto-generated method stub

				}

				public void onProviderEnabled(String provider) {
					// TODO Auto-generated method stub

				}

				public void onStatusChanged(String provider, int status,
						Bundle extras) {
					// TODO Auto-generated method stub

				}
			};
			
			LocationManager locationManager = (LocationManager) context
					.getSystemService(Context.LOCATION_SERVICE);

			SharedPreferences SP = PreferenceManager
					.getDefaultSharedPreferences(context);
			if (SP.getBoolean("activeupdate", false)) {

				if (!locationManager.isProviderEnabled(LocationManager.GPS_PROVIDER)) {
					Toast.makeText(
							context,
							context.getResources().getString(
									R.string.msg_gpsdisabled),
							Toast.LENGTH_SHORT).show();
					locationManager.requestLocationUpdates(
							LocationManager.NETWORK_PROVIDER, 0, 0,
							locationListener);
				} else {
					// GPS enabled
					locationManager.requestLocationUpdates(
							LocationManager.GPS_PROVIDER, 0, 0,
							locationListener); // workaround,
												// requestSingleUpdate: only
												// API Versions >= 9
				}


			} else {
				// get update without gps
				locationManager.requestLocationUpdates(
						LocationManager.NETWORK_PROVIDER, 0, 0,
						locationListener);
			}
			
			//cancel updates after timeout with no fix
			AlarmManager alarmManager = (AlarmManager) context
					.getSystemService(Context.ALARM_SERVICE);
			Intent i = new Intent(context, LocationReceiver.class);
			i.putExtra("de.h3ndrik.openlocation.cancelgps", "true");
			PendingIntent pendingIntent = PendingIntent
					.getBroadcast(context, 0, i,
							PendingIntent.FLAG_UPDATE_CURRENT);
			alarmManager.set(AlarmManager.RTC_WAKEUP,
					System.currentTimeMillis() + 20000,
					pendingIntent);
			Log.d(DEBUG_TAG, "cancelUpdates alarm set");
		}
		else {
			Log.d(DEBUG_TAG, "conflicting locationListener, skipping");
		}
	}

	public static void cancelUpdates(final Context context) {
		if (locationListener != null) {
			Log.d(DEBUG_TAG, "removing locationListener");

			LocationManager locationManager = (LocationManager) context
					.getSystemService(Context.LOCATION_SERVICE);
			locationManager.removeUpdates(locationListener);
			
			locationListener = null;
			
			//TODO: Request network location if GPS failed
		}
		else {
			Log.d(DEBUG_TAG, "no locationListener present");
		}
		
	}


	public LocationReceiver() {
	}
}
