package de.h3ndrik.openlocation;

import de.h3ndrik.openlocation.util.Utils;
import android.app.AlarmManager;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.location.LocationManager;
import android.os.SystemClock;
import android.preference.PreferenceManager;
import android.widget.Toast;

public class StartupReceiver extends BroadcastReceiver {
	public StartupReceiver() {
	}

	@Override
	public void onReceive(Context context, Intent intent) {

		Utils.startReceiver(context, false);

	}
}
