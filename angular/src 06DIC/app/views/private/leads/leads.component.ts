// ANGULAR
  import { Component, OnInit, Input, ViewChild } from '@angular/core';
  import { ActivatedRoute } from '@angular/router';
  import { select, Store } from '@ngrx/store';
  import { Observable } from 'rxjs/internal/Observable';

// COMPONENTS  
  import { DerivationSelectionComponent } from "./components/modals/derivation-selection.component";

// MODELS
  import { LeadModel } from '../../../core/models/lead.model';

// SERVICIOS
  import { LeadsService } from "../../../core/services/leads/leads.service";
  import { SellerService } from "../../../core/services/seller/seller.service";

// NGRX
  import { AppState } from '../../../store/app.reducers';

import * as LeadsActions from '../../../store/leads/actions/leads.action';
import * as fromLeads from '../../../store/leads/index';
import { tap } from 'rxjs/operators';
import { async } from 'q';
import { SellerModel } from 'src/app/core/models/seller.model';
import { SupervisorModel } from 'src/app/core/models/supervisor.model';
import { GerenteModel } from 'src/app/core/models/gerente.model';
import { SupervisorService } from 'src/app/core/services/supervisor/supervisor.service';
import { GerenteService } from 'src/app/core/services/gerente/gerente.service';

@Component({
  selector: 'app-leads',
  templateUrl: './leads.component.html',
  styleUrls: ['./leads.component.css']
})
export class LeadsComponent implements OnInit {
  
  @ViewChild('selection_derivation') seleccion_derivacion;
  @ViewChild('selection_criterio') seleccion_criterio;

  selectedDerivationId: 0;
  selectedCriterioId: "";
  opcionCriterio: any[] = [];

  leads_$: LeadModel[];
  Leads$: Observable<any>;
  leadsCopy$: LeadModel[];
  
  
  sellersCopy: SellerModel[] = [];
  supervisores: SupervisorModel[] = [];
  gerentes: GerenteModel[] = [];
  id_seller: number;

  gerentes_selected: string[] = [];

  supervisores_selected: string[] = [];

  vendedores: string[] = [];
  
  criterios: string[] = [
    'origen',
    'suborigen',
    'canal',
    'campaña',
    'tipoventa',
    'url',
    'producto',
    'telefono',
    'ubicacion'
  ]
  options_criterios : string[] = [];
  
  x// INTERFACE
  channels: string[] = [];  
  origins: string[] = [];
  suborigins: string[] = [];
  campanas: string[] = [];
  type_sales: string[] = [];
  products: string[] = [];
  phones: string[] = [];
  ubicacion: string[] = [];

  resultsLength$: Observable<number>;
  derivation_modal : boolean = false;
  pulse_dev: string = 'pulse';
  pulse_criterio : string = 'pulse';
  alert_danger_dev: boolean = false;
  alert_danger_criterio: boolean = false;
  alert_users_selected: boolean = false;
  selected_options_criterios: any[] = [];
  md_12: number[] = [0];

  size: number = 0;

  constructor(private activatedRoute: ActivatedRoute,
              private store: Store<AppState>,
              private leadService: LeadsService,
              private sellerService: SellerService,
              private supervisorServise: SupervisorService,
              private gerenteService: GerenteService) { }

  setLeadsCopy( leads: LeadModel[] ){
    console.log('ANTES DE LA ASIGNACIÓN DE LEADSCOPY',leads);
    this.leadsCopy$ = leads['entities'];
    console.log('ASIGNACIÓN PARA LEADSCOPY',this.leadsCopy$);
  }

  ngOnInit() {
    
    const accionLoad = new LeadsActions.Load({page: 1, filter: ''});
    this.store.dispatch( accionLoad );

    this.Leads$ = this.store.pipe(
      select(fromLeads.selectLeadState),
      tap(leads => {
        console.log( 'DENTRO DEL PIPE de LEADS SIDENAV: ' , leads );
        // this.setLeadsCopy(leads);
      })
    );

    this.leadService.available_leads({page: 1, filter: ''}).subscribe(
      data =>{
        this.leadsCopy$ = data.leads.data;
      }
    );

    this.sellerService.getSellers({ page: 1, filter: '' }).subscribe(
      data =>{
        this.sellersCopy = data.sellers.data;
        console.log(data);
      }
    );

    this.supervisorServise.getSupervisores( { page: 1, filter: '' }  ).subscribe(
      data =>{
        this.supervisores = data.supervisores.data;
      }
    );

    this.gerenteService.getGerentes( { page: 1, filter: '' } ).subscribe(
      data => {
        console.log('DE HECTOR = ', data);
        this.gerentes = data.gerentes.data;
      }
    );

    this.leadService.options_criterios().subscribe(
      data =>{
        this.channels = data.channels;
        this.origins = data.origins;
        this.campanas = data.campanas;
        this.type_sales = data.type_sales;
        this.products = data.products;
        this.phones = data.phones;
        this.ubicacion = data.address;
        this.suborigins = data.suborigins;
        console.log('OPCIONES CRITERIOS = ',data);
      }
    );
  }

  set_option(option: any){
    
    // console.log('OPTION = ',option);
    // console.log('opcionCriterio: ',this.opcionCriterio);
    // console.log('OPCION DE CRITERIO =  ',option.value, option.id),
    
    // console.log('CON ARGUMENTO', this.opcionCriterio[option].length);
    if( !this.selected_options_criterios[0] && option === 'origen'){
      this.selected_options_criterios[0] = '-0:'+this.opcionCriterio[option][this.opcionCriterio[option].length-1];      
      
    }else{
      if( !this.selected_options_criterios[1] && option === 'suborigen' ){
        
        this.selected_options_criterios[1] = '-1:'+this.opcionCriterio[option][this.opcionCriterio[option].length-1];
      }else{
        if( !this.selected_options_criterios[2] && option === 'canal' ){
          
          this.selected_options_criterios[2] = '-2:'+this.opcionCriterio[option][this.opcionCriterio[option].length-1];
        }else{
          if( !this.selected_options_criterios[3] && option === 'campaña' ){
            
            this.selected_options_criterios[3] = '-3:'+this.opcionCriterio[option][this.opcionCriterio[option].length-1];            
          }else{
            if( !this.selected_options_criterios[4] && option === 'tipoventa' ){

              this.selected_options_criterios[4] = '-4:'+this.opcionCriterio[option][this.opcionCriterio[option].length-1];

            }else{
              if( !this.selected_options_criterios[5] && option === 'url' ){
                
                this.selected_options_criterios[5] = '-5:'+this.opcionCriterio[option][this.opcionCriterio[option].length-1];

              }else{
                if( !this.selected_options_criterios[6] && option === 'producto' ){
                  
                  this.selected_options_criterios[6] = '-6:'+this.opcionCriterio[option][this.opcionCriterio[option].length-1];

                }else{
                  if( !this.selected_options_criterios[7] && option === 'telefono' ){
                    this.selected_options_criterios[7] = '-7:'+this.opcionCriterio[option][this.opcionCriterio[option].length-1];
                  }else{
                    if( !this.selected_options_criterios[8] && option === 'ubicacion' ){
                      
                      this.selected_options_criterios[8] = '-8:'+this.opcionCriterio[option][this.opcionCriterio[option].length-1];

                    }else{
                      if( this.selected_options_criterios[0] && option === 'origen'){
                        this.selected_options_criterios[0] = this.selected_options_criterios[0] + ',' + this.opcionCriterio[option][this.opcionCriterio[option].length-1];
                      }else{
                        if( this.selected_options_criterios[1] && option === 'suborigen' ){        
                          this.selected_options_criterios[1] = this.selected_options_criterios[1] + ',' + this.opcionCriterio[option][this.opcionCriterio[option].length-1];        
                        }else{
                          if( this.selected_options_criterios[2] && option === 'canal' ){
                            this.selected_options_criterios[2] = this.selected_options_criterios[2] + ',' + this.opcionCriterio[option][this.opcionCriterio[option].length-1];          
                          }else{
                            if( this.selected_options_criterios[3] && option === 'campaña' ){
                              this.selected_options_criterios[3] = this.selected_options_criterios[3] + ',' + this.opcionCriterio[option][this.opcionCriterio[option].length-1];            
                            }else{
                              if( this.selected_options_criterios[4] && option === 'tipoventa' ){
                                this.selected_options_criterios[4] = this.selected_options_criterios[4] + ',' + this.opcionCriterio[option][this.opcionCriterio[option].length-1];              
                              }else{
                                if( this.selected_options_criterios[5] && option === 'url' ){
                                  this.selected_options_criterios[5] = this.selected_options_criterios[5] + ',' + this.opcionCriterio[option][this.opcionCriterio[option].length-1];                
                                }else{
                                  if( this.selected_options_criterios[6] && option === 'producto' ){
                                    this.selected_options_criterios[6] = this.selected_options_criterios[6] + ',' + this.opcionCriterio[option][this.opcionCriterio[option].length-1];                  
                                  }else{
                                    if( this.selected_options_criterios[7] && option === 'telefono' ){
                                      this.selected_options_criterios[7] = this.selected_options_criterios[7] + ',' + this.opcionCriterio[option][this.opcionCriterio[option].length-1];
                                    }else{
                                      if( !this.selected_options_criterios[8] && option === 'ubicacion' ){
                                        this.selected_options_criterios[8] = this.selected_options_criterios[8] + ',' + this.opcionCriterio[option][this.opcionCriterio[option].length-1];
                                      }
                                    }
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    

    console.log('SELECIONES = ',this.selected_options_criterios);    
  }

  show_modal(){
    // if( this.pulse_dev === 'pulse' ){
    //   this.derivation_modal = false;      
    //   this.alert_users_selected = false;
    //   this.alert_danger_criterio = false;
    //   this.alert_danger_dev = true;
    // }else{
    console.log('SELECTED OPTIONS = ',this.selected_options_criterios);
    console.log('CRITERIOS = ',this.options_criterios);
    this.size = 0;

      if( this.pulse_criterio === 'pulse' ){
        this.derivation_modal = false;      
        this.alert_users_selected = false;
        this.alert_danger_criterio = true;
        this.alert_danger_dev = false;
      }else{
        if( (this.gerentes_selected.length + this.supervisores_selected.length + this.vendedores.length) === 0){
          this.derivation_modal = false;
          this.alert_danger_dev = false;
          this.alert_danger_criterio = false;
          this.alert_users_selected = true;
        }else{
          this.alert_users_selected = false;
          this.alert_danger_dev = false;
          this.alert_danger_criterio = false;

          if(this.selected_options_criterios.length === this.options_criterios.length){
            this.derivation_modal = true;
          }else{
                        
            this.selected_options_criterios.forEach(element => {
                if(element)
                {
                  this.size++;
                }
            });

            if(this.size != this.options_criterios.length){
              this.derivation_modal = false;
              // this.alert_users_selected = false;
              this.alert_danger_criterio = true;
              this.alert_danger_dev = false;
            }else{
              // this.alert_users_selected = false;
              this.alert_danger_dev = false;
              this.alert_danger_criterio = false;
              this.derivation_modal = true;  
            }
          }
        }
      }
    // }
  }

  gerente_selected( gerente: string ){
    let id_gerente = gerente+1;
    let aux = this.gerentes_selected.find(x => x === id_gerente);    
    
    if(aux){
      let index = this.gerentes_selected.indexOf(aux);
      if (index > -1) {
        this.gerentes_selected.splice(index, 1);
      }
    }else{
      this.gerentes_selected.push(id_gerente);      
    }
  }

  supervisor_selected( supervisor: string ){
    let id_supervisor = supervisor+1;
    let aux = this.supervisores_selected.find(x => x === id_supervisor);    
    
    if(aux){
      let index = this.supervisores_selected.indexOf(aux);
      if (index > -1) {
        this.supervisores_selected.splice(index, 1);
      }
    }else{
      this.supervisores_selected.push(id_supervisor);      
    }
  }

  vendedor_selected( vendedor: string ){
    let id_vendedor = vendedor;
    let aux = this.vendedores.find(x => x === id_vendedor);    
    
    if(aux){
      let index = this.vendedores.indexOf(aux);
      if (index > -1) {
        this.vendedores.splice(index, 1);
      }
    }else{
      this.vendedores.push(id_vendedor);      
    }
  }  

  selectOption(){    
    
    let aux = this.selectedCriterioId;
    this.options_criterios = [];

    let aux_md_12 = aux.length % 3;

    for (let idx = 0; idx < aux_md_12; idx++) {
      this.md_12[idx] = idx + 1;
    }
    
    for (let index = 0; index < aux.length; index++) {

      this.options_criterios.push(aux[index]);

    }    
    if(this.selectedDerivationId >= 1){
      this.pulse_dev = "";
    }
    if(this.options_criterios.length >= 1){
      this.pulse_criterio = "";
    }
    if(!this.selectedDerivationId){
      this.pulse_dev = "pulse";
    }
    if(this.options_criterios.length == 0){
      this.pulse_criterio = "pulse";
    }    
  }
}
