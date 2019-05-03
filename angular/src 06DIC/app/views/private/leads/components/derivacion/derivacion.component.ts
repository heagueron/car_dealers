import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';

// SERVICIOS
  import { LeadsService } from "../../../../../core/services/leads/leads.service";
  import { LeadModel } from 'src/app/core/models/lead.model';

@Component({
  selector: 'app-derivacion',
  templateUrl: './derivacion.component.html',
  styleUrls: ['./derivacion.component.css']
})
export class DerivacionComponent implements OnInit {

  leads: LeadModel[] = [];
  seleted_leads_ids = [];
  selectedDerivationId: 0;

  // INTERFAZ
  pulse_dev: string = 'pulse';
  alert_by_derivation: boolean = false;
  alert_by_empty_leads: boolean = false;
  modal_flag: boolean  = false;
  

  constructor(private leadsService: LeadsService) { }

  ngOnInit() {
    this.leadsService.available_leads({page: 1, filter: ''}).subscribe(
      data =>{
        this.leads = data.leads.data;
        console.log('TMA = ',this.leads);
      }
    );
  }

  // SELECCION DE LEADS DE LA TABLA

  selected_lead( row: any ){
    let id_lead = row.id;
    let aux = this.seleted_leads_ids.find(x => x === id_lead);    
    
    if(aux){
      let index = this.seleted_leads_ids.indexOf(aux);
      if (index > -1) {
        this.seleted_leads_ids.splice(index, 1);
      }
    }else{
      this.seleted_leads_ids.push(id_lead);      
    }
  }

  // AL ESCOGER UNA DERIVACIÓN, DESACTIVA EL EFECTO PULSE
  deactivate_pulse(){
    if(this.selectedDerivationId){
      this.pulse_dev = "";
    }else{
      this.pulse_dev = "pulse";
    }
  }

  // MUESTRA EL MODAL AL PRESIONAR EL BONTÓN +
  show_modal(){
    if(this.selectedDerivationId){
      
      this.alert_by_derivation = false;
      this.alert_by_empty_leads = false;
      this.modal_flag = false;
      
      if(this.seleted_leads_ids.length === 0){
        this.alert_by_empty_leads = true;
        this.alert_by_derivation = false;
        this.modal_flag = false;
      }else{
        this.alert_by_empty_leads = false;
        this.alert_by_derivation = false;
        // MOSTRAR MODAL
        this.modal_flag = true;

      }

    }else{
      this.alert_by_derivation = true;
      this.alert_by_empty_leads = false;
      this.modal_flag = false;
    }    
  }

}
