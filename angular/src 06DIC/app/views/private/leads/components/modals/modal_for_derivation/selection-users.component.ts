import { Component, OnInit, Input } from '@angular/core';

// SERVICIOS
  import { SellerService } from 'src/app/core/services/seller/seller.service';
  import { GerenteService } from 'src/app/core/services/gerente/gerente.service';
  import { SupervisorService } from 'src/app/core/services/supervisor/supervisor.service';


// MODELOS
  import { SellerModel } from 'src/app/core/models/seller.model';
  import { GerenteModel } from 'src/app/core/models/gerente.model';
  import { SupervisorModel } from 'src/app/core/models/supervisor.model';

@Component({
  selector: 'app-selection-users',
  templateUrl: './selection-users.component.html',
  styleUrls: ['./selection-users.component.css']
})
export class SelectionUsersComponent implements OnInit {

  @Input() leads;
  @Input() derivacion;

  sellers: SellerModel[] = [];
  gerentes: GerenteModel[] = [];
  supervisores: SupervisorModel[] = [];
  
  style_opacidad: string = 'opacidad';
  vendedores: string[] = [];
  gerentes_selected: string[] = [];
  supervisores_selected: string[] = [];

  constructor( private sellerService: SellerService,
               private gerenteService: GerenteService,
               private supervisorService: SupervisorService ) {  }

  ngOnInit() {
    this.sellerService.getSellers({ page: 1, filter: '' }).subscribe(
      data =>{
        this.sellers = data.sellers.data;
      }
    );

    this.gerenteService.getGerentes( { page: 1, filter: '' } ).subscribe(
      data =>{
        console.log('DEL SERVICIO = ',data);
        this.gerentes = data.gerentes.data;
      }
    );

    this.supervisorService.getSupervisores( { page: 1, filter: '' } ).subscribe(
      data =>{
        this.supervisores = data.supervisores.data;
      }
    );
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

  set_enpoind(){
    console.log('LEADS:', this.leads);
    console.log('MÉTODO: ',this.derivacion);
    console.log('USUARIOS: ',this.gerentes_selected, this.supervisores_selected, this.vendedores);
  }

}
