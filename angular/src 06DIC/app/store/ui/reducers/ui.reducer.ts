//ACTIONS
    import { UIActionsUnion, UIActionTypes } from '../actions/ui.action';

export interface State {
    isLoading: boolean;
    sidebar_right: boolean;
}

const initialState: State = {
    isLoading: false,
    sidebar_right: false,
};

export function UIReducer(state = initialState, action: UIActionsUnion){
    switch (action.type) {
        case UIActionTypes.enable_loading: {
            return {
                ...state,
                isLoading: true,
            };
        }

        case UIActionTypes.disable_loading: {
            return {
                ...state,
                isLoading: false,
            };
        }

        case UIActionTypes.enable_sidebar_right: {
            return {
                ...state,
                sidebar_right: true,
            };
        }

        case UIActionTypes.disable_sidebar_right: {
            return {
                ...state,
                sidebar_right: false,
            };
        }

        case UIActionTypes.ResetState: {
            return initialState;
        }

        default: {
            return state;
        }
    }
}
