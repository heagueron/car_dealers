//AUTH
    import { AuthEffects } from './auth/effects/auth.effects';

//UI
    import { UIEffects } from './ui/effects/ui.effects';

//PRODUCTS
    import { ProductsEffects } from './products/effects/products.effects';

//SALES
    import { SalesEffects } from './sales/effects/sales.effects';

//CONTACTS (RECEPTION)
import { ContactsEffects } from './contacts/effects/contacts.effects';
    

export const AppEffects: any[] = [ 
    AuthEffects,
    ProductsEffects,
    SalesEffects,
    ContactsEffects,
    UIEffects
];