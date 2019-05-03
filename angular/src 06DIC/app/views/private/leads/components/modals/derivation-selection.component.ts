import { Component, OnInit, Input } from '@angular/core';
import { LeadsService } from "../../../../../core/services/leads/leads.service";

@Component({
  selector: 'app-derivation-selection',
  templateUrl: './derivation-selection.component.html',
  styleUrls: ['./derivation-selection.component.css']
})
export class DerivationSelectionComponent implements OnInit {
  @Input() leads : any = [];
  @Input() criterios: string[] = [];
  @Input() opciones_criterios: string[] = [];
  @Input() gerentes: string[];
  @Input() supervisores: string[];
  @Input() vendedores: string[];
  @Input() selectedDerivationId: string = '';



  aux_opciones: string[] = this.opciones_criterios;

  constructor( private leadsService: LeadsService ) { }

  ngOnInit() {
    
  }

  set_endpoint(){
    // this.criterios.forEach(element => {
    //   console.log('CRITERIOS: ', element);
    //   console.log('OPCIONES DEl CRITERIO: ', this.opciones_criterios[element]);
    // });

    console.log('OPCIONES CRITERIOS = ', this.opciones_criterios);

    console.log('DERIVACIONES = ', this.selectedDerivationId);

    console.log('USUARIOS: ',this.gerentes, this.supervisores, this.vendedores);

    this.leadsService.asign_leads_seller( { 
      criterios: this.criterios, 
      opciones_criterios: this.opciones_criterios,
      gerentes: this.gerentes,
      supervisores: this.supervisores,
      vendedores: this.vendedores,
      derivaciones: this.selectedDerivationId
    } ).subscribe(
      data =>{
        console.log('DE LA ASIGNACION : ', data);
      }
    );
  }

}
