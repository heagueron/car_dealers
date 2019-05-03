import { Component, OnInit } from '@angular/core';
import { select, Store } from '@ngrx/store';
import { AppState } from 'src/app/store/products';

@Component({
  selector: 'app-contact-stats',
  templateUrl: './contact-stats.component.html',
  styleUrls: ['./contact-stats.component.css']
})
export class ContactStatsComponent implements OnInit {

  calls : number = 0;
  visits: number = 0;
  administrationCalls : number = 0;
  administrationVisits: number = 0;
  savingPlans   : number = 0;
  services : number = 0;
  parts : number = 0;

  constructor( public store : Store<AppState>) { }

  ngOnInit() {
    
    this.store.select('contacts').subscribe( contactos => {
      console.log(`datos en stats: ${contactos.data}`);

      this.calls  = contactos.data.filter( item => item.channel_id==3).length;
      this.visits = contactos.data.filter( item => item.channel_id==4).length;

      this.administrationCalls = contactos.data
          .filter( item => (item.channel_id==3 && item.reason_id==8)).length;
      this.administrationVisits = contactos.data
          .filter( item => (item.channel_id==4 && item.reason_id==8)).length;

      this.savingPlans  = contactos.data.filter( item => item.reason_id==5).length;
      this.services     = contactos.data.filter( item => item.reason_id==9).length;
      this.parts        = contactos.data.filter( item => item.reason_id==10).length;
      
    });


  }





}
