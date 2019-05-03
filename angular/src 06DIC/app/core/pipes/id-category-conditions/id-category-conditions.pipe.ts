import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'idCategoryConditions'
})
export class IdCategoryConditionsPipe implements PipeTransform {

  transform(items: any, filter: number): any {
    if (!items || !filter) {
      return items;
    }

    return items.filter(item => item.id_category === filter);
  }
}
