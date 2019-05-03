import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class PaginationService {
	offset: number = 5;

	constructor() {
		//
	}

	getPagination(current_page_pagination: number, last_page_pagination: number, to_pagination: number) {
		if(!to_pagination){
			return [];
		}

		let from = current_page_pagination - this.offset; 
		if(from < 1){
			from = 1;
		}

		let to = from + (this.offset * 2); 
		if(to >= last_page_pagination){
			to = last_page_pagination;
		}

		let pages = [];
		
		while(from <= to){
			pages.push({num: from});
			from++;
		}

		return {
			current_page: current_page_pagination,
			last_page: last_page_pagination,
			from: from,
			to: to,
			pages: pages
		};
	}
}
