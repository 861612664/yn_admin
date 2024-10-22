import Element from 'zrender/lib/Element';
import { DataModel, ECEventData, BlurScope, InnerFocus, SeriesDataType, ComponentMainType, ComponentItemTooltipOption } from './types';
/**
 * ECData stored on graphic element
 */
export interface ECData {
    dataIndex?: number;
    dataModel?: DataModel;
    eventData?: ECEventData;
    seriesIndex?: number;
    dataType?: SeriesDataType;
    focus?: InnerFocus;
    blurScope?: BlurScope;
    componentMainType?: ComponentMainType;
    componentIndex?: number;
    componentHighDownName?: string;
    tooltipConfig?: {
        name: string;
        option: ComponentItemTooltipOption<unknown>;
    };
}
export declare const getECData: (hostObj: Element<import("zrender/lib/Element").ElementProps>) => ECData;
