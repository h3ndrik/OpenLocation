package de.h3ndrik.openlocation;

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

	public static abstract class Contract implements BaseColumns {

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
		
		public static final Integer MARKED_UPLOADED = 1;
		public static final Integer MARKED_JITTER = 2;

		private static final String COMMA_SEP = ", ";
		private static final String SQL_CREATE_ENTRIES = "CREATE TABLE "
				+ DBAdapter.Contract.TABLE_NAME + " ("
				+ DBAdapter.Contract._ID
				+ " INTEGER PRIMARY KEY AUTOINCREMENT" + COMMA_SEP
				+ DBAdapter.Contract.COLUMN_TIME + " BIGINT"
				+ COMMA_SEP + DBAdapter.Contract.COLUMN_LATITUDE
				+ " DOUBLE" + COMMA_SEP
				+ DBAdapter.Contract.COLUMN_LONGITUDE + " DOUBLE"
				+ COMMA_SEP + DBAdapter.Contract.COLUMN_ALTITUDE
				+ " DOUBLE" + COMMA_SEP
				+ DBAdapter.Contract.COLUMN_ACCURACY + " FLOAT"
				+ COMMA_SEP + DBAdapter.Contract.COLUMN_SPEED
				+ " FLOAT" + COMMA_SEP
				+ DBAdapter.Contract.COLUMN_BEARING + " FLOAT"
				+ COMMA_SEP + DBAdapter.Contract.COLUMN_PROVIDER
				+ " TEXT" + COMMA_SEP
				+ DBAdapter.Contract.COLUMN_UPLOADED + " INT"
				+ " )";

		private static final String SQL_DELETE_ENTRIES = "DROP TABLE IF EXISTS "
				+ DBAdapter.Contract.TABLE_NAME;

		// Prevents the Contract class from being instantiated.
		private Contract() {
		}

	}

	SQLiteDatabase db;
	DBHelper dbhelper;

	public DBAdapter(Context context) {
		dbhelper = new DBHelper(context);
	}
	
	public class Markings {
		protected Marking[] array;
		protected Integer currentindex = 0;
		protected class Marking {
			public Long time;
			public Integer value;
		}
		public Markings(Integer size) {
			array = new Marking[size];
			currentindex = 0;

		}
		public void put(Long time, Integer value) {
			Marking marking = new Marking();
			array[currentindex] = marking;
			array[currentindex].time = time;
			array[currentindex].value = value;
			currentindex++;
		}
		public Long getPreviousTimestamp() {
			if (currentindex < 1) return null;
			return array[currentindex-1].time;
		}
		public Long getTimeAt(Integer i) {
			return array[i].time;
		}
		public Integer getMarkingAt(Integer i) {
			return array[i].value;
		}
		public Integer length() {
			return currentindex;
		}
		public void setMarkingAt(Integer i, Integer value) {
			array[i].value = value;
		}
		
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
			db.execSQL(DBAdapter.Contract.SQL_CREATE_ENTRIES);
			Log.d(DEBUG_TAG, "Database created");
		}

		public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
			// This database is only a cache for online data, so its upgrade
			// policy is
			// to simply to discard the data and start over
			db.execSQL(DBAdapter.Contract.SQL_DELETE_ENTRIES);
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
			values.put(DBAdapter.Contract.COLUMN_TIME, time);
			values.put(DBAdapter.Contract.COLUMN_LATITUDE,
					latitude);
			values.put(DBAdapter.Contract.COLUMN_LONGITUDE,
					longitude);
			values.put(DBAdapter.Contract.COLUMN_ALTITUDE,
					altitude);
			values.put(DBAdapter.Contract.COLUMN_ACCURACY,
					accuracy);
			values.put(DBAdapter.Contract.COLUMN_SPEED, speed);
			values.put(DBAdapter.Contract.COLUMN_BEARING, bearing);
			values.put(DBAdapter.Contract.COLUMN_PROVIDER,
					provider);
			values.put(DBAdapter.Contract.COLUMN_UPLOADED, 0);
			return db.insert(DBAdapter.Contract.TABLE_NAME, null,
					values);
		}

		public Cursor getLocalLocations() {
			// TODO: getReadableDatabase() ?
			return db.query(DBAdapter.Contract.TABLE_NAME,
					new String[] { DBAdapter.Contract.COLUMN_TIME,
							DBAdapter.Contract.COLUMN_LATITUDE,
							DBAdapter.Contract.COLUMN_LONGITUDE,
							DBAdapter.Contract.COLUMN_ALTITUDE,
							DBAdapter.Contract.COLUMN_ACCURACY,
							DBAdapter.Contract.COLUMN_SPEED,
							DBAdapter.Contract.COLUMN_BEARING,
							DBAdapter.Contract.COLUMN_PROVIDER,
							DBAdapter.Contract.COLUMN_UPLOADED },
					DBAdapter.Contract.COLUMN_UPLOADED + "= 0",
					null, null, null, null, null);
		}

		public Cursor getAllLocations() {
			// TODO: getReadableDatabase() ?
			return db.query(DBAdapter.Contract.TABLE_NAME,
					new String[] { DBAdapter.Contract.COLUMN_TIME,
							DBAdapter.Contract.COLUMN_LATITUDE,
							DBAdapter.Contract.COLUMN_LONGITUDE,
							DBAdapter.Contract.COLUMN_ALTITUDE,
							DBAdapter.Contract.COLUMN_ACCURACY,
							DBAdapter.Contract.COLUMN_SPEED,
							DBAdapter.Contract.COLUMN_BEARING,
							DBAdapter.Contract.COLUMN_PROVIDER,
							DBAdapter.Contract.COLUMN_UPLOADED },
					null, null, null, null, null, null);
		}

		public void markDone(Markings markings) {
			ContentValues values = new ContentValues();
			for (Integer i = 0; i < markings.length(); i++) {
				if (markings.getTimeAt(i) == null || markings.getTimeAt(i) == 0)
					continue;
				values.clear();
				values.put(DBAdapter.Contract.COLUMN_UPLOADED, markings.getMarkingAt(i));
				db.update(DBAdapter.Contract.TABLE_NAME, values,
						DBAdapter.Contract.COLUMN_TIME + " IN (" + markings.getTimeAt(i) + ")", null);
			}
		}

		public void deleteLocations(String arg) {
			db.delete(DBAdapter.Contract.TABLE_NAME,
					DBAdapter.Contract.COLUMN_TIME + " IN (" + arg
							+ ")", null);
		}

		public long lastUpdateMillis() {
			Cursor cursor = db.query(false,
					DBAdapter.Contract.TABLE_NAME,
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
