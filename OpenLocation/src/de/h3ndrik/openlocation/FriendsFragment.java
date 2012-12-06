package de.h3ndrik.openlocation;

import com.actionbarsherlock.app.SherlockListFragment;

import de.h3ndrik.openlocation.util.Server;
import de.h3ndrik.openlocation.util.Server.API;

import android.app.Activity;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.ListFragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ListView;

public class FriendsFragment extends SherlockListFragment {
	private static final String DEBUG_TAG = "FriendsFragment"; // for logging purposes

	OnFriendSelectedListener mCallback;
	
	// The container Activity must implement this interface so the fragment can deliver messages
    public interface OnFriendSelectedListener {
        /** Called by FriendsFragment when a list item is selected */
        public void onFriendSelected(String focus);
    }
	
	@Override
	public void onCreate(Bundle savedInstanceState) {
	    super.onCreate(savedInstanceState);
	    
	    Server server = new Server();
        server.init(getActivity());
        Server.API api = server.new API();
	    String[] friends = api.getFriends();
	    setListAdapter(new ArrayAdapter<String>(getActivity(), android.R.layout.simple_list_item_1, friends));
	}
	
	@Override
	public void onAttach(Activity activity) {
		super.onAttach(activity);
		
        // This makes sure that the container activity has implemented
        // the callback interface. If not, it throws an exception.
        try {
            mCallback = (OnFriendSelectedListener) activity;
        } catch (ClassCastException e) {
            throw new ClassCastException(activity.toString()
                    + " must implement OnFriendSelectedListener");
        }
	}
	
	@Override
    public void onListItemClick(ListView l, View v, int position, long id) {
        // Notify the parent activity of selected item
        mCallback.onFriendSelected((String)l.getItemAtPosition(position));
        
        // Set the item as checked to be highlighted when in two-pane layout
        getListView().setItemChecked(position, true);
    }
}
