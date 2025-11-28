import SectionFive from "./sections/SectionFive";
import SectionFour from "./sections/SectionFour";
import SectionOne from "./sections/SectionOne";
import SectionTwo from "./sections/SectionTwo";

export default function Main() {
  return (
    <main className="flex-1 p-8 space-y-4">
       <section className="flex justify-center">
        <SectionOne />
      </section>
      <section className="flex justify-center">
        <SectionTwo />
      </section>
      <section className="flex justify-center">
        <SectionFour />
      </section>
      <section className="flex justify-center">
        <SectionFive />
      </section>
    </main>
  );
}
