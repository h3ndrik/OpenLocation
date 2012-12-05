package de.h3ndrik.openlocation;

import android.app.Activity;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.ListFragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ListView;

public class FriendsFragment extends ListFragment {
	OnFriendSelectedListener mCallback;
	
	// The container Activity must implement this interface so the fragment can deliver messages
    public interface OnFriendSelectedListener {
        /** Called by FriendsFragment when a list item is selected */
        public void onFriendSelected(String focus);
    }
	
	@Override
	public void onCreate(Bundle savedInstanceState) {
	    super.onCreate(savedInstanceState);
	    setListAdapter(new ArrayAdapter<String>(getActivity(), android.R.layout.simple_list_item_single_choice));
	    /*setListAdapter(ArrayAdapter.createFromResource(getActivity()
	            .getApplicationContext(), R.array.friends,
	            R.layout.list_friends));*/
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
