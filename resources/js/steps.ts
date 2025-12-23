// types/steps.ts

export type StepId = 1 | 2 | 3 | 4 | 5;

export type Step1Data = {
  retailerName: string;
  phoneNumber?: string;
  countryCode?: string;
  category: string;
  logoFile: File | null;
  licenseFile: File | null;
};

export type Step2Data = {
  paymentMethod: "bank" | "cliq" | "";
  bankName: string ;
  iban: string ;
  cliqNumber: string ;
};

// export type Branch = "group" | "single" | "";

export type DiscountType = {
  percentage: number;
  buyOneGet: boolean;
  applyToAll: boolean;
  startDate: Date | null;
  endDate: Date | null;
};

export type GroupData = {
  brandName: string;
  description: string;
  position: { lat: number; lng: number };
};

export type Step3Data = {
//   branch: Branch;
  groups: GroupData[];
};

export type Step4Data = {
  subscription: string;
};

export type Step5Data = {
  paymentOption: "cash" | "card" | "cliq" | "";
  cardName?: string;
  cardNumber?: string;
  cardCvv?: string;
  cardExpiry?: string;
  cliqNumber?: string;
};

export type FormDataType = {
  step1: Step1Data;
  step2: Step2Data;
  step3: Step3Data;
  step4: Step4Data;
  step5: Step5Data;
};
