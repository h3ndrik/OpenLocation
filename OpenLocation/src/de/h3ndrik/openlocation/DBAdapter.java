package de.h3ndrik.openlocation;

import java.util.HashMap;
import java.util.List;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.provider.BaseColumns;
import android.util.Log;

public class DBAdapter {
	private static final String DEBUG_TAG = "DBAdapter"; // for logging purposes

	public static abstract class LocationCacheContract implements BaseColumns {

		public static final String TABLE_NAME = "locationsCache";
		public static final String COLUMN_TIME = "time";
		public static final String COLUMN_LATITUDE = "latitude";
		public static final String COLUMN_LONGITUDE = "longitude";
		public static final String COLUMN_ALTITUDE = "altitude";
		public static final String COLUMN_ACCURACY = "accuracy";
		public static final String COLUMN_SPEED = "speed";
		public static final String COLUMN_BEARING = "bearing";
		public static final String COLUMN_PROVIDER = "provider";
		public static final String COLUMN_UPLOADED = "uploaded";

		private static final String COMMA_SEP = ", ";
		private static final String SQL_CREATE_ENTRIES = "CREATE TABLE "
				+ DBAdapter.LocationCacheContract.TABLE_NAME + " ("
				+ DBAdapter.LocationCacheContract._ID
				+ " INTEGER PRIMARY KEY AUTOINCREMENT" + COMMA_SEP
				+ DBAdapter.LocationCacheContract.COLUMN_TIME + " BIGINT"
				+ COMMA_SEP + DBAdapter.LocationCacheContract.COLUMN_LATITUDE
				+ " DOUBLE" + COMMA_SEP
				+ DBAdapter.LocationCacheContract.COLUMN_LONGITUDE + " DOUBLE"
				+ COMMA_SEP + DBAdapter.LocationCacheContract.COLUMN_ALTITUDE
				+ " DOUBLE" + COMMA_SEP
				+ DBAdapter.LocationCacheContract.COLUMN_ACCURACY + " FLOAT"
				+ COMMA_SEP + DBAdapter.LocationCacheContract.COLUMN_SPEED
				+ " FLOAT" + COMMA_SEP
				+ DBAdapter.LocationCacheContract.COLUMN_BEARING + " FLOAT"
				+ COMMA_SEP + DBAdapter.LocationCacheContract.COLUMN_PROVIDER
				+ " TEXT" + COMMA_SEP
				+ DBAdapter.LocationCacheContract.COLUMN_UPLOADED + " INT"
				+ " )";

		private static final String SQL_DELETE_ENTRIES = "DROP TABLE IF EXISTS "
				+ DBAdapter.LocationCacheContract.TABLE_NAME;

		// Prevents the Contract class from being instantiated.
		private LocationCacheContract() {
		}

	}

	SQLiteDatabase db;
	DBHelper dbhelper;

	public DBAdapter(Context context) {
		dbhelper = new DBHelper(context);
	}

	public class DBHelper extends SQLiteOpenHelper {
		private static final String DEBUG_TAG = "DBHelper"; // for logging
															// purposes
		// If you change the database schema, you must increment the database
		// version.
		public static final int DATABASE_VERSION = 1;
		public static final String DATABASE_NAME = "OpenLocation.db";

		public DBHelper(Context context) {
			super(context, DATABASE_NAME, null, DATABASE_VERSION);
		}

		public void onCreate(SQLiteDatabase db) {
			db.execSQL(DBAdapter.LocationCacheContract.SQL_CREATE_ENTRIES);
			Log.d(DEBUG_TAG, "Database created");
		}

		public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
			// This database is only a cache for online data, so its upgrade
			// policy is
			// to simply to discard the data and start over
			db.execSQL(DBAdapter.LocationCacheContract.SQL_DELETE_ENTRIES);
			onCreate(db);
		}

		public void onDowngrade(SQLiteDatabase db, int oldVersion,
				int newVersion) {
			onUpgrade(db, oldVersion, newVersion);
		}

		// Eigene Methoden
		public DBHelper open_w() throws SQLException {
			db = this.getWritableDatabase();
			return this;
		}

		public DBHelper open_r() throws SQLException {
			db = this.getReadableDatabase();
			return this;
		}

		public void close() {
			db.close();
		}

		public long insertLocation(long time, double latitude,
				double longitude, double altitude, float accuracy, float speed,
				float bearing, String provider) {
			ContentValues values = new ContentValues();
			values.put(DBAdapter.LocationCacheContract.COLUMN_TIME, time);
			values.put(DBAdapter.LocationCacheContract.COLUMN_LATITUDE,
					latitude);
			values.put(DBAdapter.LocationCacheContract.COLUMN_LONGITUDE,
					longitude);
			values.put(DBAdapter.LocationCacheContract.COLUMN_ALTITUDE,
					altitude);
			values.put(DBAdapter.LocationCacheContract.COLUMN_ACCURACY,
					accuracy);
			values.put(DBAdapter.LocationCacheContract.COLUMN_SPEED, speed);
			values.put(DBAdapter.LocationCacheContract.COLUMN_BEARING, bearing);
			values.put(DBAdapter.LocationCacheContract.COLUMN_PROVIDER,
					provider);
			values.put(DBAdapter.LocationCacheContract.COLUMN_UPLOADED, 0);
			return db.insert(DBAdapter.LocationCacheContract.TABLE_NAME, null,
					values);
		}

		public Cursor getLocalLocations() {
			// TODO: getReadableDatabase() ?
			return db.query(DBAdapter.LocationCacheContract.TABLE_NAME,
					new String[] { DBAdapter.LocationCacheContract.COLUMN_TIME,
							DBAdapter.LocationCacheContract.COLUMN_LATITUDE,
							DBAdapter.LocationCacheContract.COLUMN_LONGITUDE,
							DBAdapter.LocationCacheContract.COLUMN_ALTITUDE,
							DBAdapter.LocationCacheContract.COLUMN_ACCURACY,
							DBAdapter.LocationCacheContract.COLUMN_SPEED,
							DBAdapter.LocationCacheContract.COLUMN_BEARING,
							DBAdapter.LocationCacheContract.COLUMN_PROVIDER,
							DBAdapter.LocationCacheContract.COLUMN_UPLOADED },
					DBAdapter.LocationCacheContract.COLUMN_UPLOADED + "= 0",
					null, null, null, null, null);
		}

		public Cursor getAllLocations() {
			// TODO: getReadableDatabase() ?
			return db.query(DBAdapter.LocationCacheContract.TABLE_NAME,
					new String[] { DBAdapter.LocationCacheContract.COLUMN_TIME,
							DBAdapter.LocationCacheContract.COLUMN_LATITUDE,
							DBAdapter.LocationCacheContract.COLUMN_LONGITUDE,
							DBAdapter.LocationCacheContract.COLUMN_ALTITUDE,
							DBAdapter.LocationCacheContract.COLUMN_ACCURACY,
							DBAdapter.LocationCacheContract.COLUMN_SPEED,
							DBAdapter.LocationCacheContract.COLUMN_BEARING,
							DBAdapter.LocationCacheContract.COLUMN_PROVIDER,
							DBAdapter.LocationCacheContract.COLUMN_UPLOADED },
					null, null, null, null, null, null);
		}

		public void markDone(LinkedHashMap<Long, Integer> args) {
			ContentValues values = new ContentValues();
			values.put(DBAdapter.LocationCacheContract.COLUMN_UPLOADED, 1);
			db.update(DBAdapter.LocationCacheContract.TABLE_NAME, values,
					DBAdapter.LocationCacheContract.COLUMN_TIME + " IN (" + arg
							+ ")", null);
		}

		public void deleteLocations(String arg) {
			db.delete(DBAdapter.LocationCacheContract.TABLE_NAME,
					DBAdapter.LocationCacheContract.COLUMN_TIME + " IN (" + arg
							+ ")", null);
		}

		public long lastUpdateMillis() {
			Cursor cursor = db.query(false,
					DBAdapter.LocationCacheContract.TABLE_NAME,
					new String[] { "time" }, null, null, null, null,
					"time DESC", "1");
			// Cursor cursor = db.rawQuery("SELECT time FROM " +
			// DBAdapter.LocationCacheContract.TABLE_NAME +
			// " ORDER BY time DESC LIMIT 1", null);
			cursor.moveToFirst();
			if (cursor.getCount() == 1) {
				return cursor.getLong(0);
			} else
				Log.d(DEBUG_TAG,
						"lastUpdate(): Expected 1 row, got "
								+ Integer.toString(cursor.getCount()));
			return -1;
		}
	}
}
